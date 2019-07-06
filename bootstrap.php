<?php

use IWGB\Join\Provider;
use Slim\Container;

define('APP_ROOT', __DIR__);

require APP_ROOT . '/vendor/autoload.php';

$c = new Container(require __DIR__ . '/settings.php');

Sentry\init([
    'dsn' => $c['settings']['sentry']['dsn'],
]);

$c->register(new Provider\Doctrine())
    ->register(new Provider\Slim())
    ->register(new Provider\HttpClient());

return $c;
