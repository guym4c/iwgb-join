<?php

namespace Iwgb\Join\Handler;

use Iwgb\Join\Handler\Api\Error\Error;
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
            return $this->errorRedirect($request, $response,
                Error::RECALLED_APPLICANT_INVALID()
            );
        }

        $this->init();
        $this->getSession()->set(ApplicantSession::APPLICANT_ID, $aid);

        return $this->redirectToRoute($response, Route::RECALL_SESSION);
    }
}