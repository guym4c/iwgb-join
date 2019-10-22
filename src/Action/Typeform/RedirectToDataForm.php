<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\Typeform;

use IWGB\Join\Action\GenericAction;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RedirectToDataForm extends GenericAction {

    private const DATA_FORM_ID = 'IRVE4B';

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant();

        if (empty($applicant)) {
            return $this->returnError($response, 'Invalid session');
        }

        return self::redirectToTypeform(self::DATA_FORM_ID, $applicant, $response);
    }

}