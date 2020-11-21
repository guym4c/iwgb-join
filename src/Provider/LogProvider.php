<?php

namespace Iwgb\Join\Provider;

use Exception;
use Iwgb\Join\Log\ApplicantEventLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LogProvider  implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function register(Container $c) {

        $c[Provider::LOG] = function () use ($c): Logger {
            $log = new Logger('applications');
            $log->pushHandler(new StreamHandler(APP_ROOT . '/var/log/applications.log', Logger::DEBUG));
            $log->pushHandler(new ApplicantEventLogHandler($c[Provider::ENTITY_MANAGER], Logger::INFO));
            return $log;
        };
    }
}