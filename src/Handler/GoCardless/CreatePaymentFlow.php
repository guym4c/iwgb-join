<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Iwgb\Join\Handler\GoCardless;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use GoCardlessPro\Core\Exception\InvalidStateException;
use Guym4c\Airtable\AirtableApiException;
use Iwgb\Join\Handler\Api\Error\Error;
use Iwgb\Join\Log\Event;
use Iwgb\Join\Route;
use Psr\Http\Message\ResponseInterface;
use Sentry;
use Slim\Http\Request;
use Slim\Http\Response;

class CreatePaymentFlow extends GenericGoCardlessAction {

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant($request);
        $applicant->setBranchDataComplete(true);
        $record = $applicant->fetchRecord($this->airtable);

        if (empty($record)) {
            return $this->errorRedirect($request, $response, Error::MMS_INTEGRATION_NO_MANDATE());
        }
        $this->em->flush();

        $plan = $this->airtable->get('Plans', $applicant->getPlan());

        try {
            $flow = $this->gocardless->redirectFlows()->create(['params' => [
                'session_token'        => $applicant->getSession(),
                'success_redirect_url' => $this->router->urlFor(Route::COMPLETE_PAYMENT),
                'description'          => "{$plan->Branch->load('Branches')->Name}: {$plan->Plan} (£{$plan->Amount})",
                'prefilled_customer'   => [
                    'email'       => $record->Email,
                    'family_name' => $record->{'Last Name'},
                    'given_name'  => $record->{'First Name'},
                    'language'    => self::parseLanguage($record->Language[0] ?? null),
                ],
            ]]);
        } catch (InvalidStateException $e) {
            Sentry\captureException($e);
            return $this->errorRedirect($request, $response, Error::PAYMENT_FAILED_NO_MANDATE());
        }

        $this->log->addInfo(Event::REDIRECT_TO_PAYMENT, [
            'applicant' => $applicant->getId(),
            'flow'      => $flow->id,
        ]);

        return $response->withRedirect($flow->redirect_url);
    }

    private static function parseLanguage(?string $language = null) {
        return empty($language)
            ? 'en'
            : [
            'English' => 'en',
            'Spanish' => 'es',
            'Portuguese' => 'pt',
        ][$language] ?? 'en';
    }
}