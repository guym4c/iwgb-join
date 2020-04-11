<?php

namespace Iwgb\Join\Handler;

use Iwgb\Join\Middleware\ApplicantSession;
use Iwgb\Join\Route;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallApplication extends AbstractSessionValidationHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $aid = $args['aid'] ?? null;
        if (empty($aid)) {
            return ApplicantSession::sessionInvalid($response, $this->sm);
        }

        $this->init();
        $this->getSession()->set(ApplicantSession::APPLICANT_ID, $aid);

        return $this->redirectToRoute($response, Route::RECALL_SESSION);
    }
}