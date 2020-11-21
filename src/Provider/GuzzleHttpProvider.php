<?php

namespace Iwgb\Join\Provider;

use GuzzleHttp;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GuzzleHttpProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {
        $c[Provider::HTTP] = fn (): GuzzleHttp\Client => new GuzzleHttp\Client();
    }
}