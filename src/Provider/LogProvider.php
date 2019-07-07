<?php


namespace IWGB\Join\Provider;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LogProvider  implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function register(Container $c) {

        $c['log'] = function (Container $c): Logger {
            $log = new Logger('log');
            $log->pushHandler(new StreamHandler(APP_ROOT . '/log/info.log', Logger::INFO));
            return $log;
        };
    }
}