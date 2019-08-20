<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action;

use Exception;
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

        $jobType = $this->airtable->search('Job types', 'Slug', $args['slug'])
                       ->getRecords()[0] ?? null;

        if (empty($jobType)) {
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);
        }

        $applicant = new Applicant();
        $this->persist($applicant)->flush();
        $this->log->addDebug('Applicant created', ['aid' => $applicant->getId()]);

        if (!$jobType->Sort) {
            /** @var Record $plan */
            $plan = $jobType->Plan->load('Plans');
            $applicant->setPlan($plan->getId());
            $applicant->setBranch($plan->Branch->load('Branches')->getId());
            $this->em->flush();

            $this->log->addDebug('Applicant placed into plan', [
                'plan' => $plan->Name,
                'aid'  => $applicant->getId(),
            ]);

            return self::redirectToTypeform($this->settings['typeform']['core-questions-id'], $applicant, $response);
        }

        return self::redirectToTypeform($jobType->{'Typeform ID'}, $applicant, $response);
    }
}