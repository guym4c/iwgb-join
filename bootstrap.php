<?php

use IWGB\Join\Provider;
use Slim\Container;

define('APP_ROOT', __DIR__);

require APP_ROOT . '/vendor/autoload.php';

$c = new Container(require __DIR__ . '/settings.php');

if (!$c->settings['dev']) {
    Sentry\init([
        'dsn' => $c['settings']['sentry']['dsn'],
    ]);
}

$c->register(new Provider\Doctrine())
    ->register(new Provider\Slim())
    ->register(new Provider\Twig())
    ->register(new Provider\HttpClient())
    ->register(new Provider\LogProvider())
    ->register(new Provider\AirtableProvider());

return $c;
