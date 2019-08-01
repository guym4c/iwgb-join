<?php

namespace IWGB\Join\Action\GoCardless;

use GoCardlessPro as GoCardless;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\ListFilter;
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

                    $payment = $this->gocardless->payments()->get($event->links->payment);
                    $mandate = $this->gocardless->mandates()->get($payment->links->mandate);

                    $member = $this->airtable->list('Members', (new ListFilter())
                        ->setFormula("SEARCH('{$mandate->links->customer}', {Customer ID})"))
                                  ->getRecords()[0];

                    $this->airtable->create('Missed Payments', [
                        'GoCardless ID' => $event->id,
                        'Reason'        => $event->details->cause,
                        'Reason detail' => $event->details->description,
                        'Member'        => [$member->getId()],
                    ]);

                    break;
            }
        }

        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);
    }
}