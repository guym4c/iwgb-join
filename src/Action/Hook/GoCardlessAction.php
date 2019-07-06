<?php

namespace IWGB\Join\Action\Hook;

use IWGB\Join\Action\GenericAction;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use GoCardlessPro as GoCardless;

class GoCardlessAction extends GenericAction {

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        try {
            $events = GoCardless\Webhook::parse($request->getParsedBody(),
                $request->getHeader('Webhook-Signature'),
                $this->settings['gocardless']['webhookSecret']);

        } catch (GoCardless\Core\Exception\InvalidSignatureException $e) {
            $this->log->addError($e->getMessage());
            return $response->withStatus(498);
        }

        foreach ($events as $event) {
            // do something
        }

        return $response->withStatus(204);
    }
}