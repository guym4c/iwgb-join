<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Iwgb\Join\Handler\Typeform;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\TypeformAPI\Model\Resource\Form;
use Guym4c\TypeformAPI\Model\Webhook\Answer;
use Guym4c\TypeformAPI\Model\Webhook\FormResponse;
use Guym4c\TypeformAPI\Model\Webhook\WebhookRequest;
use Guym4c\TypeformAPI\Typeform;
use Guym4c\TypeformAPI\TypeformApiException;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Domain\SorterResult;
use Iwgb\Join\Log\ApplicantEventLogProcessor;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Teapot\StatusCode;

class Sorter extends AbstractTypeformHandler {

    /**
     * {@inheritdoc}
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws AirtableApiException
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $event = $request->getMethod() === 'GET' && !$this->settings['isProd']
            ? $this->generateMockEvent($request)
            : Typeform::parseWebhook($request, $this->settings['typeform']['webhookSecret']);

        $this->log->addDebug('Received Typeform event', ['event' => $event->id]);

        if (
            $this->settings['isProd']
            && !$event->valid
        ) {
            $this->log->addError('Typeform event signature invalid', ['event' => $event->id]);
            return $response->withStatus(StatusCode::UNAUTHORIZED);
        }

        if ($event->eventType != 'form_response') {
            return $response->withStatus(StatusCode::NO_CONTENT);
        }

        if (!$this->parseTypeformEvent($event->formResponse)) {
            $this->log->addError('No sorting result found');
        }

        $redirect = $request->getQueryParam('redirect');
        return empty($redirect)
            ? $response->withStatus(StatusCode::NO_CONTENT)
            : $response->withRedirect($redirect);
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
        $applicant = $this->em->find(Applicant::class, $form->hidden['aid'] ?? '');

        if (empty($applicant)) {
            $this->log->addError('Applicant/AID not found in response', [
                'form'      => $form->formId,
                'submitted' => $form->submittedAt->format(DATE_ATOM),
            ]);
            return false;
        }

        $this->log->pushProcessor(new ApplicantEventLogProcessor($applicant));

        $answer = end($form->answers);

        $sortingResult = $this->findResultByQuestion($answer);

        if (empty($sortingResult)) {
            $this->log->addError('Unable to sort applicant', [
                'form'      => $form->formId,
                'submitted' => $form->submittedAt->format(DATE_ATOM),
            ]);
            return false;
        }

        $applicant->setPlan(
            $sortingResult->getPlan()
        );

        $plan = $sortingResult->fetchPlan($this->airtable);

        $this->log->addInfo('Applicant sorted into plan', [
            'plan'      => $plan->getId(),
            'h_plan'    => $plan->Name,
            'applicant' => $applicant->getId(),
        ]);

        $this->em->flush();

        return true;
    }

    private function findResultByQuestion(Answer $answer): ?SorterResult {

        foreach ($this->em->getRepository(SorterResult::class)->findAll() as $result) {

            /** @var SorterResult $result */

            $conditional = $result->getConditional();
            if (in_array($conditional, ['true', 'false'])) {
                $conditional = $conditional === 'true';
            } else {
                $conditional = strval($conditional);
            }

            if (
                $result->getQuestion() == $answer->field->id
                && (
                    $conditional === $answer->answer
                    || (
                        is_array($answer->answer)
                        && $conditional === $answer->answer['label'] ?? null
                    )
                )
            ) {
                return $result;
            }
        }

        return null;
    }

    private const METADATA_QUERY_KEYS = [
        'aid' => true,
        'form' => true,
        'redirect' => true,
    ];

    /**
     * @param Request $request
     * @return WebhookRequest
     * @throws TypeformApiException
     */
    private function generateMockEvent(Request $request): WebhookRequest {
        $formId = $request->getQueryParam('form');
        $form = Form::get($this->typeform, $formId);

        $fieldsJson = [];
        foreach ($form->fields as $field) {
            $fieldsJson[] = [
                'id' => $field->id,
                'title' => $field->title,
                'description' => $field->description,
                'ref' => $field->ref,
            ];
        }

        $definition = [
            'title' => $form->title,
            'fields' => $fieldsJson,
        ];

        $answesFromQuery = array_diff_key($request->getQueryParams(), self::METADATA_QUERY_KEYS);
        $answersJson = [];
        foreach ($answesFromQuery as $fieldId => $answer) {
            $answersJson[] = [
                'text' => $answer,
                'field' => ['id' => $fieldId],
            ];
        }

        return new WebhookRequest([
            'event_id' => uniqid(),
            'event_type' => 'form_response',
            'form_response' => [
                'form_id' => $formId,
                'token' => '',
                'landed_at' => 'now',
                'submitted_at' => 'now',
                'hidden' => ['aid' => $request->getQueryParam('aid')],
                'definition' => $definition,
                'answers' => $answersJson,
            ],
        ]);
    }

}