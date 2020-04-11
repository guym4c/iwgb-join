<?php

namespace Iwgb\Join\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Teapot\StatusCode;

class Health extends RootHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        return $response->withStatus(StatusCode::NO_CONTENT);
    }
}