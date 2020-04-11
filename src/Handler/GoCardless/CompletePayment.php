<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Iwgb\Join\Handler\GoCardless;

use Doctrine\ORM;
use GoCardlessPro\Core\Exception\InvalidStateException;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\RedirectFlow;
use GoCardlessPro\Resources\Subscription;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\Record;
use Iwgb\Join\Domain\AirtablePlanRecord;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Handler\Api\Error\Error;
use Iwgb\Join\Log\Event;
use Iwgb\Join\Middleware\ApplicantSession;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Sentry;

class CompletePayment extends GenericGoCardlessAction {

    private const FLOW_ID_PARAM_KEY = 'redirect_flow_id';
    private const AIRTABLE_CONFIRMED_STATUS = 'Member';
    private const BASE_PAYMENT_REFERENCE = 'Iwgb';
    public const CONFIRMATION_REDIRECT_URL = 'https://iwgb.org.uk/page/confirmation';

    /**
     * {@inheritDoc}
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant($request);

        $flowId = $request->getQueryParam(self::FLOW_ID_PARAM_KEY);

        if (empty($flowId)) {
            $this->log->addError(Event::FLOW_ID_MISSING);
            $this->em->flush();

            return $this->errorRedirect($request, $response,
                Error::NO_GC_FLOW_ID_PROVIDED()
            );
        }

        // retrieve flow
        $flow = $this->gocardless->redirectFlows()->get($flowId);

        // check session from gocardless
        if ($applicant->getSession() !== $flow->session_token) {
            $this->log->addError(Event::GOCARDLESS_SESSION_MISMATCH, [
                'flow' => $flow->id,
            ]);
            $this->em->flush();

            return $this->errorRedirect($request, $response,
                Error::CSRF_GC_SESSION_MISMATCH()
            );
        }

        $this->log->addDebug('Completing redirect flow', [
            'flow'      => $flow->id,
        ]);

        // complete flow
        try {
            $this->gocardless->redirectFlows()->complete($flow->id, ['params' => [
                'session_token' => $applicant->getSession(),
            ]]);
        } catch (InvalidStateException $e) {
            Sentry\captureException($e);
            return $this->errorRedirect($request, $response,
                Error::PAYMENT_FAILED_NO_MANDATE()
            );
        }

        // update flow
        $flow = $this->gocardless->redirectFlows()->get($flow->id);

        // populate airtable
        try {
            $this->updateMemberRecord($applicant, $flow);
        } catch (AirtableApiException $e) {
            Sentry\captureException($e);
            return $this->errorRedirect($request, $response,
                Error::MMS_INTEGRATION_NO_MANDATE()
            );
        }

        // get plan and branch
        try {
            $plan = new AirtablePlanRecord(
                $this->airtable->get('Plans', $applicant->getPlan()));

            $branch = $this->airtable->get('Branches', $plan->getBranchId());
        } catch (AirtableApiException $e) {
            Sentry\captureException($e);
            return $this->errorRedirect($request, $response,
                Error::MMS_INTEGRATION_MANDATE_CREATED()
            );
        }

        // create subscription
        try {
            $this->createSubscription($plan, $branch,
                $this->gocardless->mandates()->get($flow->links->mandate)
            );
        } catch (InvalidStateException $e) {
            Sentry\captureException($e);
            return $this->errorRedirect($request, $response,
                Error::PAYMENT_FAILED_MANDATE_CREATED()
            );
        }

        $this->sm->destroy();

        $applicant->setPaymentComplete(true);
        $this->em->flush();

        return $response->withRedirect(self::CONFIRMATION_REDIRECT_URL);
    }

    /**
     * @param Applicant    $applicant
     * @param RedirectFlow $flow
     * @return void
     * @throws AirtableApiException
     */
    private function updateMemberRecord(Applicant $applicant, RedirectFlow $flow): void {

        try {
            $record = $applicant->fetchRecord($this->airtable);
        } catch (AirtableApiException $e) {
            throw new AirtableApiException("(no mandate): {$e->getMessage()}");
        }

        $bankAccount = $this->gocardless->customerBankAccounts()->get($flow->links->customer_bank_account);
        $customer = $this->gocardless->customers()->get($flow->links->customer);

        $record->{'Customer ID'} = $customer->id;
        $record->{'Address L1'} = $customer->address_line1;
        $record->{'Address L2'} = $customer->address_line2;
        $record->{'Address L3'} = $customer->address_line3;
        $record->{'Address city'} = $customer->city;
        $record->Postcode = $customer->postal_code;
        $record->Bank = $bankAccount->bank_name;
        $record->{'Bank account'} = "******{$bankAccount->account_number_ending}";
        $record->Status = self::AIRTABLE_CONFIRMED_STATUS;

        try {
            $this->airtable->update($record);
        } catch (AirtableApiException $e) {
            throw new AirtableApiException("(mandate created): {$e->getMessage()}");
        }

        $this->log->addInfo('Updated applicant as Confirmed', [
            'record'    => $record->getId(),
        ]);
    }

    /**
     * @param AirtablePlanRecord $plan
     * @param Record             $branch
     * @param Mandate            $mandate
     * @return Subscription
     * @throws InvalidStateException
     */
    private function createSubscription(AirtablePlanRecord $plan, Record $branch, Mandate $mandate): Subscription {

        $planName = "{$branch->Name}: {$plan->getPlanName()}";

        $subscription = $this->gocardless->subscriptions()->create(['params' => array_merge([
            'amount'   => $plan->getAmount() * 100,
            'currency' => 'GBP',
            'name'     => $planName,
//          'payment_reference' => self::BASE_PAYMENT_REFERENCE . '-' . $branch->{'Payment reference'},
            'links'    => [
                'mandate' => $mandate->id,
            ],
        ], $plan->getGoCardlessIntervalFormat())]);

        $this->log->addInfo('Created subscription', [
            'subscription' => $subscription->id,
        ]);

        return $subscription;
    }
}