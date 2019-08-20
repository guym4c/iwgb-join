<?php
/** @noinspection PhpUndefinedFieldInspection */

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

        $applicant = $this->getApplicant($args);

        if (empty($applicant)) {
            return $this->returnError($response, 'Invalid session');
        }

        $branch = $this->airtable->get('Branches', $applicant->getBranch());
        $plan = $this->airtable->get('Plans', $applicant->getPlan());

        $this->log->addDebug('Redirecting applicant to Branch form', [
            'applicant' => $applicant->getId(),
            'branch' => $branch->Name,
        ]);

        return self::redirectToTypeform($branch->{'Typeform ID'}, $applicant, $response, [
            'amount' => $plan->Amount,
        ]);
    }
}