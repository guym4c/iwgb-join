<?php

namespace IWGB\Join\Provider;


use IWGB\Join\Action;
use IWGB\Join\TypeHinter;
use Pimple\ServiceProviderInterface;
use Slim\App;
use Pimple\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Slim implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['slim'] = function (Container $c): App {
            /** @var $c TypeHinter */
            $app = new App($c);

            $app->add(function (Request $request, Response $response, callable $next) {
                $uri = $request->getUri();
                $path = $uri->getPath();
                if ($path != '/' && substr($path, -1) == '/') {
                    $uri = $uri->withPath(substr($path, 0, -1));

                    if($request->getMethod() == 'GET') {
                        return $response->withRedirect((string)$uri, 301);
                    } else {
                        return $next($request->withUri($uri), $response);
                    }
                }
                
                return $next($request, $response);
            });

            $app->get('/apply/{slug}', Action\CreateApplication::class);

            $app->group('/callback', function (App $app) {

                $app->post('/typeform/sorter', Action\Typeform\Sorter::class);
            });

            return $app;
        };
    }
}