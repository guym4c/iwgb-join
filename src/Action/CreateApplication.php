<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action;

use Exception;
use Guym4c\Airtable\ListFilter;
use Guym4c\Airtable\Record;
use IWGB\Join\Domain\Applicant;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CreateApplication extends GenericAction {

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $jobType = $this->airtable->list('Job types', (new ListFilter())
            ->setFormula("SEARCH('{$args['slug']}', {Slug})"))
                      ->getRecords()[0] ?? null;

        if (empty($jobType)) {
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);
        }

        $applicant = new Applicant();
        $this->persist($applicant)->flush();
        $this->session->set(self::SESSION_AID_KEY, $applicant->getId());

        if (!$jobType->Sort) {
            /** @var Record $plan */
            $plan = $jobType->Plan->load('Plans');
            $applicant->setPlan($plan->getId());
            $applicant->setBranch($plan->Branch->load('Branches')->getId());
            $this->em->flush();

            return self::redirectToTypeform($this->settings['typeform']['core-questions-id'], $applicant, $response);
        }

        return self::redirectToTypeform($jobType->{'Typeform ID'}, $applicant, $response);
    }
}