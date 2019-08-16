<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\Typeform;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\TypeformAPI\Model\Webhook\Answer;
use Guym4c\TypeformAPI\Model\Webhook\FormResponse;
use Guym4c\TypeformAPI\Typeform;
use IWGB\Join\Config;
use IWGB\Join\Domain\Applicant;
use IWGB\Join\JsonConfigObject;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

class Sorter extends GenericTypeformAction {

    const NO_SORTING_RESULT_FOUND_MSG = "No matching plan was found for the applicant's answers";

    private $results;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->results = JsonConfigObject::getItems(Config::SorterResults);
    }

    /**
     * {@inheritdoc}
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws AirtableApiException
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $event = Typeform::parseWebhook($request, $this->settings['typeform']['webhookSecret']);

        $this->log->addDebug('Received Typeform event', ['event' => $event->id]);

        if (!$event->valid) {
            $this->log->addNotice('Typeform event signature invalid', ['event' => $event->id]);
            return $response->withStatus(StatusCode::HTTP_UNAUTHORIZED);
        }

        if ($event->eventType != 'form_response') {
            return $response->withStatus(StatusCode::HTTP_NO_CONTENT);
        }

        if (!$this->parseTypeformEvent($event->formResponse)) {
            $this->log->addError(self::NO_SORTING_RESULT_FOUND_MSG);
        }

        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);
    }

    /**
     * @param FormResponse $form
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws AirtableApiException
     */
    private function parseTypeformEvent(FormResponse $form): bool {
        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->find($form->hidden['aid']);

        if (empty($applicant)) {
            $this->log->addError('AID not found in response', [
                'form'      => $form->formId,
                'submitted' => $form->submittedAt->format(DATE_ATOM),
            ]);
            return false;
        }

        $answer = end($form->answers);

        $sortingResult = $this->findResultByQuestion($answer);

        if (empty($sortingResult)) {
            $this->log->addError('Unable to sort applicant', [
                'form'      => $form->formId,
                'submitted' => $form->submittedAt->format(DATE_ATOM),
                'applicant' => $applicant->getId(),
            ]);
            return false;
        }

        $applicant->setPlan($sortingResult['plan-id']);
        $plan = $this->airtable->get('Plans', $sortingResult['plan-id']);
        $applicant->setBranch($plan->Branch->load('Branches')->getId());
        $this->em->flush();

        $this->log->addDebug('Applicant sorted into plan', [
            'plan'      => $plan->Name,
            'applicant' => $applicant->getId(),
        ]);

        return true;
    }

    private function findResultByQuestion(Answer $answer): array {

        foreach ($this->results as $result) {

            $condition = $result['condition'];
            if (in_array($condition, ['true', 'false'])) {
                $condition = $condition == 'true'
                    ? true
                    : false;
            } else {
                $condition = strval($condition);
            }

            if ($result['question-id'] == $answer->field->id &&
                $condition == $answer->answer) {
                return $result;
            }
        }

        return [];
    }

}