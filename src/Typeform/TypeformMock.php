<?php

namespace Iwgb\Join\Typeform;

use Slim\Http\Request;
use Slim\Http\Response;

class TypeformMock {

    private string $formId;
    private Request $request;
    private Response $response;
    private array $query = [];

    /**
     * TypeformMock constructor.
     * @param string $formId
     * @param Request $request
     * @param Response $response
     * @param array $query
     */
    public function __construct(
        string $formId,
        Request $request,
        Response $response,
        array $query
    ) {
        $this->formId = $formId;
        $this->request = $request;
        $this->response = $response;
        $this->query = $query;
    }

    public function getMock() {

    }
}