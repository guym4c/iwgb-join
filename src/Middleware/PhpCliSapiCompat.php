<?php

namespace Iwgb\Join\Middleware;

use Iwgb\Join\Provider\Provider;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Router;

class PhpCliSapiCompat extends AbstractMiddleware {

    private Router $router;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->router = $c[Provider::ROUTER];
    }

    public function __invoke(Request $request, Response $response, callable $next) {
        /** @var Uri $uri */
        $uri = $request->getUri();
        $processedRequest = $request;

        if (php_sapi_name() === 'cli-server') {
            $this->router->setBasePath('');
            $path = substr("{$uri->getBasePath()}{$uri->getPath()}", 0, -1);
            $processedRequest = $request->withUri(
                $request->getUri()->withPath($path)
            );
        }

        return $next($processedRequest, $response);
    }
}