<?php

use Slim\App;
use Slim\Container;

error_reporting(E_ALL ^ E_WARNING);

/** @var Container $container */
$container = require_once __DIR__ . '/../bootstrap.php';

/** @var App $app */
$app = $container['slim'];

/** @noinspection PhpUnhandledExceptionInspection */
$app->run();