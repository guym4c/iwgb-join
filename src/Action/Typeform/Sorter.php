<?php

namespace IWGB\Join\Action\Typeform;

use Guym4c\TypeformAPI\Model\FormResponse;
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

    private $results;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->results = JsonConfigObject::getItems(Config::SorterResults);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $event = Typeform::parseWebhook($request, $this->settings['typeform']['webhookSecret']);

        if (!$event->valid)
            return $response->withStatus(StatusCode::HTTP_UNAUTHORIZED);

        if ($event->eventType != 'form_response')
            return $response->withStatus(StatusCode::HTTP_NO_CONTENT);

        $error = $this->parseTypeformEvent($event->formResponse, $response);
        if ($error != null)
            return $error;

        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);
    }

    private function parseTypeformEvent(FormResponse $form, Response $response): ?ResponseInterface {
        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->find($form->hidden['aid']);

        if (empty($applicant))
            return $response->withStatus(StatusCode::HTTP_NO_CONTENT);

        $answer = end($form->answers);
        $sortingResult = $this->findResultByQuestionId($answer->field->id);

        //TODO

    }

    private function findResultByQuestionId(string $id): ?array {

        foreach ($this->results as $result) {
            if ($result['question-id'] == $id)
                return $result;
        }

        return null;
    }

}