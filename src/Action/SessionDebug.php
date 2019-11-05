<?php

namespace IWGB\Join\Action;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class SessionDebug extends GenericAction {

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        return $response->withJson([$this->session->get(self::SESSION_AID_KEY)]);
    }
}