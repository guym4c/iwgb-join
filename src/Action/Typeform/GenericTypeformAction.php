<?php

namespace IWGB\Join\Action\Typeform;

use Guym4c\TypeformAPI\Typeform;
use IWGB\Join\Action\GenericAction;
use IWGB\Join\Domain\Applicant;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Response;

abstract class GenericTypeformAction extends GenericAction {

    protected $typeform;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->typeform = new Typeform($this->settings['typeform']['api']);
    }

    public static function redirectToTypeform(string $formId, Applicant $applicant, Response $response): ResponseInterface {
        return $response->withRedirect(sprintf("%s/{$formId}?aid={$applicant->getId()}",
            self::TYPEFORM_FORM_BASE_URL));
    }
}