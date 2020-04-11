<?php

namespace Iwgb\Join\Handler;

use Iwgb\Join\Route;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CreateSession extends AbstractSessionValidationHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $this->sm->clear();

        $this->init();
        $this->getSession()->set('jobType', $args['slug']);

        return $this->redirectToRoute($response, Route::CREATE_APPLICATION);
    }
}