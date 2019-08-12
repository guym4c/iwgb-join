<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\GoCardless;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use GoCardlessPro\Core\Exception\InvalidStateException;
use Guym4c\Airtable\AirtableApiException;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CreateRedirectFlow extends GenericGoCardlessAction {

    const SUCCESS_REDIRECT_URL = 'https://members.iwgb.org.uk/callback/gocardless/success';

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     * @throws InvalidStateException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant();
        $record = $applicant->fetchRecord($this->airtable);
        $this->em->flush();

        $plan = $this->airtable->get('Plans', $applicant->getPlan());

        $flow = $this->gocardless->redirectFlows()->create(['params' => [
            'session_token'        => $applicant->getSession(),
            'success_redirect_url' => self::SUCCESS_REDIRECT_URL,
            'description'          => "{$plan->Branch->load('Branches')->Name}: {$plan->Plan} (£{$plan->Amount})",
            'prefilled_customer'   => [
                'email'       => $record->Email,
                'family_name' => $record->{'Last Name'},
                'given_name'  => $record->{'First Name'},
            ],
        ]]);

        return $response->withRedirect($flow->redirect_url);
    }
}