<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Guym4c\Airtable\AirtableApiException;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallBranch extends GenericAction {

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant();

        if (empty($applicant)) {
            return $this->returnError($response, 'Invalid session');
        }

        $applicant->setCoreDataComplete(true);
        $this->em->flush();

        $branch = $this->airtable->get('Branches', $applicant->getBranch());
        $plan = $this->airtable->get('Plans', $applicant->getPlan());

        $this->log->addDebug('Redirecting applicant to Branch form', [
            'applicant' => $applicant->getId(),
            'branch' => $branch->Name,
        ]);

        if (empty($branch->{'Typeform ID'})) {
            return $response->withRedirect('/join/pay');
        }

        return self::redirectToTypeform($branch->{'Typeform ID'}, $applicant, $response, [
            'amount' => $plan->Amount,
        ]);
    }
}