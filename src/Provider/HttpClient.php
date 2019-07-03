<?php

namespace IWGB\Join\Provider;

use Buzz\Browser;
use Nyholm\Psr7\Factory\Psr17Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Buzz\Client;

class HttpClient implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {
        $c['http'] = new Browser(
            new Client\MultiCurl(['allow_redirects' => true],
                new Psr17Factory())
        );
    }
}