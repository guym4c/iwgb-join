<?php

namespace Iwgb\Join\Handler\GoCardless;

use GoCardlessPro;
use GoCardlessPro\Environment;
use Iwgb\Join\Handler\RootHandler;
use Iwgb\Join\Provider\Provider;
use Slim\Container;

abstract class AbstractGoCardlessHandler extends RootHandler {

    protected GoCardlessPro\Client $goCardless;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->goCardless = new GoCardlessPro\Client([
            'access_token' => $c[Provider::SETTINGS]['gocardless']['accessToken'],
            'environment' => Environment::LIVE,
        ]);
    }

    protected static function parseLanguage(?string $language = null) {
        return empty($language)
            ? 'en'
            : [
                'English' => 'en',
                'Spanish' => 'es',
                'Portuguese' => 'pt',
            ][$language] ?? 'en';
    }

}