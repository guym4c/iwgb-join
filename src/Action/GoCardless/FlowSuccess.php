<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\GoCardless;

use GoCardlessPro\Core\Exception\InvalidStateException;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\RedirectFlow;
use GoCardlessPro\Resources\Subscription;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\Record;
use IWGB\Join\Domain\AirtablePlanRecord;
use IWGB\Join\Domain\Applicant;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Sentry;

class FlowSuccess extends GenericGoCardlessAction {

    private const FLOW_ID_PARAM_KEY = 'redirect_flow_id';
    private const AIRTABLE_CONFIRMED_STATUS = 'Member';
    private const BASE_PAYMENT_REFERENCE = 'IWGB';
    private const CONFIRMATION_REDIRECT_URL = 'https://iwgb.org.uk/page/info/confirmation';

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        // check for Flow ID in query
        if (empty($request->getQueryParam(self::FLOW_ID_PARAM_KEY))) {
            $this->log->addNotice('Payment page called with no Flow ID');
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);
        }

        // retrieve flow
        $flow = $this->gocardless->redirectFlows()
            ->get($request->getQueryParam(self::FLOW_ID_PARAM_KEY));

        // retrieve applicant from session
        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->findOneBy(['session' => $flow->session_token]);

        $this->log->addDebug('Completing redirect flow', [
            'applicant' => $applicant->getId(),
            'flow'      => $flow->id,
        ]);

        // complete flow
        try {
            $this->gocardless->redirectFlows()->complete($flow->id, ['params' => [
                'session_token' => $applicant->getSession(),
            ]]);
        } catch (InvalidStateException $e) {
            Sentry\captureException($e);
            return $this->returnError($response, 'Cannot process payment, invalid state');
        }

        // update flow params
        $flow = $this->gocardless->redirectFlows()
            ->get($request->getQueryParam(self::FLOW_ID_PARAM_KEY));

        // populate airtable
        try {
            $this->updateMemberRecord($applicant, $flow);
        } catch (AirtableApiException $e) {
            Sentry\captureException($e);
            return $this->returnError($response, ('MMS integration error'));
        }

        // get plan and branch
        try {
            $plan = new AirtablePlanRecord(
                $this->airtable->get('Plans', $applicant->getPlan()));

            $branch = $this->airtable->get('Branches', $plan->getBranchId());
        } catch (AirtableApiException $e) {
            Sentry\captureException($e);
            return $this->returnError($response, 'MMS integration error (mandate created)');
        }

        // create subscription
        try {
            $this->createSubscription($applicant, $plan, $branch,
                $this->gocardless->mandates()->get($flow->links->mandate));

        } catch (InvalidStateException $e) {
            Sentry\captureException($e);
            return $this->returnError($response, 'Payment processing error');
        }

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

        $this->log->addDebug('Updated applicant as Confirmed', [
            'applicant' => $applicant->getId(),
            'record'    => $record->getId(),
        ]);
    }

    /**
     * @param Applicant          $applicant
     * @param AirtablePlanRecord $plan
     * @param Record             $branch
     * @param Mandate            $mandate
     * @return Subscription
     * @throws InvalidStateException
     */
    private function createSubscription(Applicant $applicant, AirtablePlanRecord $plan, Record $branch, Mandate $mandate): Subscription {

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

        $this->log->addDebug('Created subscription', [
            'applicant'    => $applicant->getId(),
            'subscription' => $subscription->id,
        ]);

        return $subscription;
    }
}