<?php


namespace IWGB\Join\Provider;

use Exception;
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

        $c['log'] = function (): Logger {
            $log = new Logger('applications');
            $log->pushHandler(new StreamHandler(APP_ROOT . '/var/log/applications.log', Logger::DEBUG));
            return $log;
        };
    }
}