<?php

namespace Iwgb\Join\Provider;

use Exception;
use Iwgb\Join\Handler\Api\Error\ErrorHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry;
use Slim\Http\Request;
use Slim\Http\Response;

class ErrorHandlerProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        $c['errorHandler'] = fn (Container $c): callable => function (
            Request $request,
            Response $response,
            Exception $e
        ) use ($c): ResponseInterface {

            Sentry\captureException($e);

            try {
                /** @var Logger $log */
                $log = $c['log'];

                if (!empty($log)) {
                    $log->addError($e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } catch (Exception $e) {}

            return ErrorHandler::redirectPreSession($response);
        };
    }
}