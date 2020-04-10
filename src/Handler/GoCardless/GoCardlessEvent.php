<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Iwgb\Join\Handler\GoCardless;

use GoCardlessPro as GoCardless;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\Record;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

class GoCardlessEvent extends GenericGoCardlessAction {

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        try {
            $events = GoCardless\Webhook::parse($request->getBody()->getContents(),
                $request->getHeader('Webhook-Signature')[0],
                $this->settings['gocardless']['webhookSecret']);

        } catch (GoCardless\Core\Exception\InvalidSignatureException $e) {
            $this->log->addError($e->getMessage());
            return $response->withStatus(498);
        }

        foreach ($events as $event) {

            $this->log->addDebug('Processing GoCardless event', [
                'event' => $event->id,
            ]);

            switch ($event->resource_type) {

                case 'subscriptions':

                    switch ($event->action) {

                        case 'cancelled':

                            $member = $this->getMemberFromSubscription($this->gocardless->subscriptions()
                                ->get($event->links->subscription));

                            if (empty($member)) {
                                break;
                            }

                            $this->log->debug('Marking member status cancelled', [
                                'event'  => $event->id,
                                'member' => $member->getId(),
                            ]);

                            $member->Status = 'Cancelled';
                            $this->airtable->update($member);
                    }

                    break;

                case 'payments':

                    switch ($event->action) {

                        case 'failed':

                            $member = $this->getMemberFromPayment($this->gocardless->payments()
                                ->get($event->links->payment));

                            if (empty($member)) {
                                break;
                            }

                            $this->log->addDebug('Adding missed payment to Airtable', [
                                'event'  => $event->id,
                                'member' => $member->getId(),
                            ]);

                            $this->airtable->create('Missed Payments', [
                                'GoCardless ID' => $event->id,
                                'Reason'        => $event->details->cause,
                                'Reason detail' => $event->details->description,
                                'Member'        => [$member->getId()],
                            ]);

                            break;
                    }

                    break;
            }
        }

        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);
    }

    /**
     * @param GoCardless\Resources\Mandate $mandate
     * @return Record|null
     * @throws AirtableApiException
     */
    private function getMemberFromMandate(GoCardless\Resources\Mandate $mandate): ?Record {
        return $this->getMemberFromCustomerID($mandate->links->customer);
    }

    /**
     * @param GoCardless\Resources\Payment $payment
     * @return Record|null
     * @throws AirtableApiException
     */
    private function getMemberFromPayment(GoCardless\Resources\Payment $payment): ?Record {
        return $this->getMemberFromCustomerID($this->gocardless->mandates()->get($payment->links->mandate)
            ->links->customer);
    }

    /**
     * @param GoCardless\Resources\Subscription $subscription
     * @return Record|null
     * @throws AirtableApiException
     */
    private function getMemberFromSubscription(GoCardless\Resources\Subscription $subscription): ?Record {
        return $this->getMemberFromCustomerID($this->gocardless->mandates()->get($subscription->links->mandate)
            ->links->customer);
    }

    /**
     * @param string $customerId
     * @return Record|null
     * @throws AirtableApiException
     */
    private function getMemberFromCustomerID(string $customerId): ?Record {
        return $this->airtable->search('Members', 'Customer ID', $customerId)
            ->getRecords()[0] ?? null;
    }
}