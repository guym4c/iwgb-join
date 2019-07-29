<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\GoCardless;

use GoCardlessPro\Core\Exception\InvalidStateException;
use GoCardlessPro\Resources\Customer;
use Guym4c\Airtable\AirtableApiException;
use IWGB\Join\Domain\AirtablePlanRecord;
use IWGB\Join\Domain\Applicant;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class FlowSuccess extends GenericGoCardlessAction {

    const FLOW_ID_PARAM_KEY = 'redirect_flow_id';
    const AIRTABLE_CONFIRMED_STATUS = 'Member';
    const BASE_PAYMENT_REFERENCE = 'IWGB';
    const CONFIRMATION_REDIRECT_URL = '';

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     * @throws InvalidStateException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        if (empty($request->getQueryParam(self::FLOW_ID_PARAM_KEY))) {
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);
        }

        $flow = $this->gocardless->redirectFlows()
            ->get($request->getQueryParam(self::FLOW_ID_PARAM_KEY));

        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->findOneBy(['session' => $flow->session_token]);
        $record = $applicant->fetchRecord($this->airtable);

        $flow = $this->gocardless->redirectFlows()
            ->complete($flow->id, $applicant->getSession());

        $bankAccount = $this->gocardless->customerBankAccounts()->get($flow->links['customer_bank_account']);
        $customer = $this->gocardless->customers()->get($flow->links['customer']);

        $record->{'Customer ID'} = $customer->id;
        $record->Address = self::parseAddress($customer);
        $record->Postcode = $customer->postal_code;
        $record->Bank = $bankAccount->bank_name;
        $record->{'Bank account'} = "******{$bankAccount->account_number_ending}";

        $record->Status = self::AIRTABLE_CONFIRMED_STATUS;
        $this->airtable->update($record);

        $plan = new AirtablePlanRecord(
            $this->airtable->get('Plans', $applicant->getMembershipType()));

        $branch = $this->airtable->get('Branches', $plan->getBranchId());

        $planName = "{$branch->Name}: {$plan->getPlanName()}";

        $this->gocardless->subscriptions()->create(['params' => array_merge([
            'amount'            => $plan->getAmount() * 100,
            'currency'          => 'GBP',
            'name'              => $planName,
            'payment_reference' => self::BASE_PAYMENT_REFERENCE . '-' . $branch->{'Payment reference'},
            'links'             => [
                'mandate' => $flow->links['mandate'],
            ],
        ], $plan->getGoCardlessIntervalFormat())]);

        return $response->withRedirect(self::CONFIRMATION_REDIRECT_URL);
    }

    private static function parseAddress(Customer $customer): string {

        $addressLines = [
            $customer->address_line1,
            $customer->address_line2,
            $customer->address_line3,
        ];

        $address = '';
        foreach ($addressLines as $line) {
            if (!empty($line)) {
                $address .= "$line,";
            }
        }
        return substr($address, 0, -1);
    }
}