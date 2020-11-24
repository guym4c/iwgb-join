<?php

namespace Iwgb\Join\Handler\Typeform;

use Guym4c\TypeformAPI\Typeform;
use Iwgb\Join\Handler\AbstractSessionValidationHandler;
use Iwgb\Join\Route;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class AbstractTypeformHandler extends AbstractSessionValidationHandler {

    protected const TYPEFORM_FORM_BASE_URL = 'https://iwgb.typeform.com/to';

    protected Typeform $typeform;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->typeform = new Typeform($this->settings['typeform']['api']);
    }

    protected function redirectToTypeform(
        string $formId,
        Request $request,
        Response $response,
        array $query = [],
        string $dataUrl = ''
    ): ResponseInterface {
        if (!$this->settings['isProd']) {
            return $this->redirectToRoute($response, Route::MOCK_FORM, array_merge($query, [
                'id' => $formId,
                'data' => $dataUrl,
            ]));
        }

        return $response->withRedirect($this->getTypeformUrl($request, $formId, $query));
    }

    protected function getTypeformUrl(Request $request, string $formId, array $query = []): string {
        $queryString = http_build_query(array_merge($query, [
            'aid' => $this->getApplicant($request)->getId(),
        ]));
        return sprintf("%s/{$formId}?{$queryString}", self::TYPEFORM_FORM_BASE_URL);
    }
}