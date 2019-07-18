<?php

namespace IWGB\Join\Action;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallBranch extends GenericAction {

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant();

        return self::redirectToTypeform(
            $this->airtable->get('Branches', $applicant->getBranch())->{'Typeform ID'},
            $applicant,
            $response);
    }
}