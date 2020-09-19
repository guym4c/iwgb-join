<?php

namespace Iwgb\Join\Handler\Api;

use Iwgb\Join\Handler\RootHandler;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Teapot\StatusCode;

class FlushCache extends RootHandler {

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        $this->airtable->flushCache();
        return $response->withStatus(StatusCode::NO_CONTENT);
    }
}