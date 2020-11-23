<?php

use Dotenv\Dotenv;
use Iwgb\Join\Provider;
use Iwgb\Join\Provider\Provider as ContainerService;
use Slim\Container;

define('APP_ROOT', __DIR__);

require APP_ROOT . '/vendor/autoload.php';

Dotenv::createImmutable(APP_ROOT)->load();

$c = new Container(require __DIR__ . '/settings.php');

if (!in_array($c[ContainerService::SETTINGS]['env'], ['dev', 'qa'])) {
    Sentry\init(['dsn' => $c[ContainerService::SETTINGS]['sentry']['dsn']]);
}

$c->register(new Provider\DoctrineOrmProvider())
    ->register(new Provider\LogProvider())
    ->register(new Provider\ErrorHandlerProvider())
    ->register(new Provider\GuzzleHttpProvider())
    ->register(new Provider\AirtableProvider())
    ->register(new Provider\GraphQLProvider())
    ->register(new Provider\AuraSessionProvider())
    ->register(new Provider\HandlebarsViewProvider());

return $c;
