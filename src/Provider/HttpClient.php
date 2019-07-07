<?php

namespace IWGB\Join\Provider;

use GuzzleHttp;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HttpClient implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['http'] = function (): GuzzleHttp\Client {
            return new GuzzleHttp\Client();
        };
    }
}