<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Iwgb\Join\Handler;

use Aura\Session\Segment;
use Exception;
use Guym4c\Airtable\Record;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Handler\Api\Error\Error;
use Iwgb\Join\Handler\Api\Error\ErrorHandler;
use Iwgb\Join\Log\ApplicantEventLogProcessor;
use Iwgb\Join\Log\Event;
use Iwgb\Join\Middleware\ApplicantSession;
use Iwgb\Join\Route;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class CreateApplication extends AbstractSessionValidationHandler {

    private Segment $session;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->session = $this->getSession();
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $jobTypeSlug = $this->session->get('jobType');

        if (!$this->validate()) {
            return $this->errorRedirect($request, $response,
                Error::SESSION_START_FAILED()
            );
        }

        if (empty($jobTypeSlug)) {
            return $this->errorRedirect($request, $response,
                Error::NO_JOB_TYPE_PROVIDED()
            );
        }

        $jobType = $this->airtable->find('Job types', 'Slug', $jobTypeSlug)[0] ?? null;

        if (empty($jobType)) {
            $this->log->addError(Event::INVALID_JOB_TYPE, [
                'slug' => $jobTypeSlug,
            ]);
            $this->em->flush();

            return $this->errorRedirect($request, $response,
                Error::JOB_TYPE_INVALID()
            );
        }

        $applicant = new Applicant();
        $this->persist($applicant)->flush();

        ApplicantSession::initialise($this->session, $applicant);
        $this->log->pushProcessor(new ApplicantEventLogProcessor($applicant));

        $this->log->addInfo(Event::APPLICANT_CREATED);

        if (!$jobType->Sort) {
            /** @var Record $plan */
            $plan = $jobType->Plan->load('Plans');
            $applicant->setPlan($plan->getId());
            $applicant->setBranch($plan->Branch->load('Branches')->getId());

            $this->log->addInfo(Event::PLAN_PLACED, [
                'plan'   => $plan->getId(),
                'h_plan' => $plan->Name,
                'aid'    => $applicant->getId(),
            ]);

            $this->em->flush();

            return $this->redirectToRoute($response, Route::CORE_DATA);
        }

        $this->em->flush();

        return self::redirectToTypeform(
            $jobType->{'Typeform ID'},
            $request->withAttribute('applicant', $applicant),
            $response
        );
    }
}