<?php

namespace IWGB\Join\Action;

use Guym4c\Airtable\AirtableApiException;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallBranch extends GenericAction {

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant();

        if (empty($applicant)) {
            return $this->returnError($response, 'Invalid session');
        }

        return self::redirectToTypeform(
            $this->airtable->get('Branches', $applicant->getBranch())->{'Typeform ID'},
            $applicant,
            $response);
    }
}