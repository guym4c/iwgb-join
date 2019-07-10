<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\GoCardless;

use GoCardlessPro\Core\Exception\InvalidStateException;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\ListFilter;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CreateRedirectFlow extends GenericGoCardlessAction {

    const SUCCESS_REDIRECT_URL = 'https://members.iwgb.org.uk/join/callback/gocardless/success';

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     * @throws InvalidStateException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant($args);
        $applicant = $this->airtable->list('Members', (new ListFilter())
            ->setFormula("{Applicant ID = \"{$applicant->getId()}\""))
                         ->getRecords()[0];

        $plan = $this->airtable->get('Plans', $applicant->getMembershipType());

        $flow = $this->gocardless->redirectFlows()->create([
            'session_token'        => $applicant->getSession(),
            'success_redirect_url' => self::SUCCESS_REDIRECT_URL,
            'description'          => "{$plan->Branch->load('Branches')->Name}: {$plan->Plan}",
            'prefilled_customer'   => [
                'email'       => $applicant->getRecord()->email,
                'family_name' => $applicant->getRecord()->{'Last Name'},
                'given_name'  => $applicant->getRecord()->{'First Name'},
            ],
        ]);

        return $response->withRedirect($flow->redirect_url);

    }
}