<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Pimple\Container;

/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';

ConsoleRunner::run(
    ConsoleRunner::createHelperSet($container['em'])
);
