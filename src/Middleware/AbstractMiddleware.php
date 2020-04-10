<?php

namespace Iwgb\Join\Middleware;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class AbstractMiddleware {

    protected Container $c;

    public function __construct(Container $c) {
        $this->c = $c;
    }

    abstract public function __invoke(Request $request, Response $response, callable $next);
}