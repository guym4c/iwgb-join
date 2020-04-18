<?php

namespace Iwgb\Join\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teapot\StatusCode;

class BearerAuthMiddleware extends AbstractMiddleware {

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
        $key = explode(' ', $request->getHeader('Authorization')[0] ?? '')[1] ?? '';
        if ($key != $this->c['settings']['api']['token']) {
            return $response->withStatus(StatusCode::UNAUTHORIZED);
        } else {
            return $next($request, $response);
        }
    }
}