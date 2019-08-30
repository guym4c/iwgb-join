<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\GoCardless;

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
            /** @var $event GoCardless\Resources\Event */

            $this->log->addDebug('Processing GoCardless event', [
                'event' => $event->id,
            ]);

            switch ($event->action) {
                case 'failed':

                    $memberId = $this->getMemberFromMandate($this->gocardless->mandates()
                        ->get($event->links->mandate))->getId();

                    $this->log->addDebug('Adding missed payment to Airtable', [
                        'event'  => $event->id,
                        'member' => $memberId,
                    ]);

                    $this->airtable->create('Missed Payments', [
                        'GoCardless ID' => $event->id,
                        'Reason'        => $event->details->cause,
                        'Reason detail' => $event->details->description,
                        'Member'        => [$memberId],
                    ]);
                    break;

                case 'cancelled':
                    $member = $this->getMemberFromMandate($this->gocardless->mandates()
                        ->get($event->links->mandate));

                    $this->log->debug('Marking member status cancelled', [
                        'event'  => $event->id,
                        'member' => $member->getId(),
                    ]);

                    $member->Status = 'Cancelled';
                    $this->airtable->update($member);
            }
        }

        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);
    }

    /**
     * @param GoCardless\Resources\Mandate $mandate
     * @return Record
     * @throws AirtableApiException
     */
    private function getMemberFromMandate(GoCardless\Resources\Mandate $mandate): Record {
        return $this->airtable->search('Members', 'Customer ID', $mandate->links->customer)
                   ->getRecords()[0];
    }
}