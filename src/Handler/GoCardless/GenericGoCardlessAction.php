<?php

namespace Iwgb\Join\Handler\GoCardless;

use GoCardlessPro;
use GoCardlessPro\Environment;
use Iwgb\Join\Handler\RootHandler;
use Iwgb\Join\Provider\Provider;
use Slim\Container;

abstract class GenericGoCardlessAction extends RootHandler {

    protected GoCardlessPro\Client $gocardless;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->gocardless = new GoCardlessPro\Client([
            'access_token' => $c[Provider::SETTINGS]['gocardless']['accessToken'],
            'environment' => Environment::LIVE,
        ]);
    }

}