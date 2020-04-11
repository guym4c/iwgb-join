<?php

$keys = require_once APP_ROOT . '/keys.php';

$env = 'dev'; // empty in production

$isDev = $env === 'dev';

$dbSuffix = empty($env) ? '' : "-{$env}";

return ['settings' => [

    'env'                               => $env,
    'isDev'                             => $isDev,
    'displayErrorDetails'               => true,
    'determineRouteBeforeAppMiddleware' => false,
    'basePath'                          => 'https://members.iwgb.org.uk',

    'gocardless'     => [
        'webhookSecret' => $keys['gocardless']['webhook'],
        'accessToken'   => $keys['gocardless']['accessToken'],
    ],
    'doctrine'       => [
        // if true, metadata caching is forcefully disabled
        'dev_mode'  => $isDev,

        // you should add any other path containing annotated entity classes
        'entityDir' => APP_ROOT . '/src/Domain',

        'connection' => array_merge([
            'driver'        => 'pdo_mysql',
            'host'          => 'db.iwgb.org.uk',
            'port'          => 25060,
            'dbname'        => "iwgb-members{$dbSuffix}",
            'charset'       => 'utf8mb4',
        ], $keys['db']),
    ],
    'typeform'       => [
        'webhookSecret'   => $keys['typeform']['webhook'],
        'api'             => $keys['typeform']['api'],
        'coreQuestionsId' => 'IRVE4B',
    ],
    'airtable'       => [
        'key'      => $keys['airtable'],
        'base'     => 'app8RK2AsBtnIcezs',
        'proxyKey' => $keys['airtableProxy'],
    ],
    'action-network' => [
        'token' => $keys['action-network'],
    ],
    'api'            => [
        'token' => $keys['api'],
    ],
    'sentry'         => [
        'dsn' => $keys['sentry'],
    ]
]];