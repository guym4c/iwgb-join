<?php

use Iwgb\Join\Provider;
use Slim\Container;

define('APP_ROOT', __DIR__);

require APP_ROOT . '/vendor/autoload.php';

$c = new Container(require __DIR__ . '/settings.php');

if (!$c['settings']['isDev']) {
    Sentry\init(['dsn' => $c['settings']['sentry']['dsn']]);
}

$c->register(new Provider\DoctrineOrmProvider())
    ->register(new Provider\LogProvider())
    ->register(new Provider\ErrorHandlerProvider())
    ->register(new Provider\GuzzleHttpProvider())
    ->register(new Provider\AirtableProvider())
    ->register(new Provider\GraphQLProvider())
    ->register(new Provider\AuraSessionProvider());

return $c;
