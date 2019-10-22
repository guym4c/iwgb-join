<?php

namespace IWGB\Join\Provider;


use IWGB\Join\Action;
use IWGB\Join\TypeHinter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Middleware\Session;
use SlimSession;

class Slim implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['session'] = function () {
            return new SlimSession\Helper();
        };

        $c['slim'] = function (Container $c): App {
            /** @var $c TypeHinter */
            $app = new App($c);

            $app->add(function (Request $request, Response $response, callable $next) {
                $uri = $request->getUri();
                $path = $uri->getPath();
                if ($path != '/' && substr($path, -1) == '/') {
                    $uri = $uri->withPath(substr($path, 0, -1));

                    if ($request->getMethod() == 'GET') {
                        return $response->withRedirect((string)$uri, 301);
                    } else {
                        return $next($request->withUri($uri), $response);
                    }
                }

                return $next($request, $response);
            });

            $app->add(new Session([
                'name'        => 'IwgbMemberSessid',
                'autorefresh' => true,
                'lifetime'    => '1 hour'
            ]));

            $app->group('/join', function (App $app) {

                $app->get('/branch', Action\RecallBranch::class);
                $app->get('/pay', Action\GoCardless\CreateRedirectFlow::class);
                $app->get('/{slug}', Action\CreateApplication::class);
            });

            $app->group('/callback', function (App $app) {

                $app->get('/gocardless/confirm', Action\GoCardless\FlowSuccess::class);
                $app->post('/typeform/sorter', Action\Typeform\Sorter::class);
                $app->post('/gocardless/event', Action\GoCardless\GoCardlessEvent::class);
            });

            return $app;
        };
    }
}