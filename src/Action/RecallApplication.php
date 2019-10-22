<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action;

use Guym4c\Airtable\AirtableApiException;
use IWGB\Join\Domain\Applicant;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallApplication extends GenericAction {

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->find($args['applicant']);

        if (empty($applicant) ||
            empty($applicant->getPlan())) {
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);
        }

        // application is live
        $this->session->set(self::SESSION_AID_KEY, $applicant->getId());

        if (!$applicant->isCoreDataComplete()) {
            return $response->withRedirect('/join/data');
        }

        if (!$applicant->isBranchDataComplete()) {
            return $response->withRedirect('/join/branch');
        }

        if (empty($applicant->fetchRecord($this->airtable)->{'Customer ID'})) {
            return $response->withRedirect('/join/pay');
        }

        // else complete
    }
}