<?php

// settings.php

$keys = require_once APP_ROOT . '/keys.php';

return [

    'dev' => true,

    'displayErrorDetails'               => true,
    'determineRouteBeforeAppMiddleware' => false,

    'gocardless' => [
        'webhookSecret' => $keys['gocardless']['webhook'],
        'accessToken'   => $keys['gocardless']['accessToken'],
    ],
    'sentry'     => [
        'dsn' => $keys['sentry'],
    ],
    'doctrine'   => [
        // if true, metadata caching is forcefully disabled
        'dev_mode'      => true,

        // path where the compiled metadata info will be cached
        // make sure the path exists and it is writable
        'cache_dir'     => APP_ROOT . '/var/doctrine',

        // you should add any other path containing annotated entity classes
        'metadata_dirs' => [APP_ROOT . '/src/Domain'],

        'connection' => array_merge([
            'driver' => 'pdo_mysql',
            'host'   => '',
            'port'   => 3306,
            'dbname' => '',
        ], $keys['db']),
    ],
    'typeform'   => [
        'webhookSecret'     => $keys['typeform']['webhook'],
        'api'               => $keys['typeform']['api'],
        'core-questions-id' => '',
    ],
    'airtable'   => [
        'key'  => $keys['airtable'],
        'base' => '',
    ],
    'twig'       => [
        'templates_dir' => APP_ROOT . '/view/',
        'cache_dir'     => APP_ROOT . '/var/twig',
        'debug'         => true,
    ],
    'api'            => [
        'token' => $keys['api'],
    ],
];