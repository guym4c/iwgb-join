<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\GoCardless;

use GoCardlessPro as GoCardless;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\ListFilter;
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


            /** @var $event GoCardless\Resources\Event */
            switch ($event->action) {
                case 'failed':

                    $this->airtable->create('Missed Payments', [
                        'GoCardless ID' => $event->id,
                        'Reason'        => $event->details->cause,
                        'Reason detail' => $event->details->description,
                        'Member'        => [$this->getMemberFromPayment($this->gocardless->payments()
                            ->get($event->links->payment))->getId()],
                    ]);
                    break;

                case 'cancelled':
                    $member = $this->getMemberFromPayment($this->gocardless->payments()
                        ->get($event->links->payment));
                    $member->Status = 'Cancelled';
                    $this->airtable->update($member);
            }
        }

        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);
    }

    /**
     * @param GoCardless\Resources\Payment $payment
     * @return Record
     * @throws AirtableApiException
     */
    private function getMemberFromPayment(GoCardless\Resources\Payment $payment): Record {
        return $this->airtable->list('Members', (new ListFilter())
            ->setFormula("SEARCH('{$this->gocardless->mandates()->get($payment->links->mandate)->links->customer}', {Customer ID})"))
                      ->getRecords()[0];
    }
}