<?php

namespace IWGB\Join\Action;

use IWGB\Join\TypeHinter;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class GenericAction {

    protected $http;

    protected $log;

    protected $settings;

    public function __construct(Container $c) {
        /** @var $c TypeHinter */

        $this->http = $c->http;
        $this->log = $c->log;
        $this->settings = $c->settings;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param string[] $args
     * @return ResponseInterface
     */
    abstract public function __invoke(Request $request, Response $response, array $args): ResponseInterface;
}