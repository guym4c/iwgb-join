<?php

namespace Iwgb\Join\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teapot\StatusCode;

class RemoveTrailingSlashes extends AbstractMiddleware {

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
        $uri = $request->getUri();
        $path = $uri->getPath();
        if ($path != '/' && substr($path, -1) == '/') {
            $uri = $uri->withPath(substr($path, 0, -1));

            if ($request->getMethod() == 'GET') {
                return $response->withRedirect((string)$uri, StatusCode::MOVED_PERMANENTLY);
            } else {
                return $next($request->withUri($uri), $response);
            }
        }

        return $next($request, $response);
    }
}