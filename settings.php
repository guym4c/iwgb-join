<?php

$env = $_ENV['ENVIRONMENT'];

$isProd = $env === 'prod';
$isQa = $env === 'qa';

$dbSuffix = $isProd ? '' : "-{$env}";

$baseUrl = $_ENV['BASE_HOST'];
if ($isQa) {
    $baseUrl = "https://qa.{$baseUrl}";
} else if ($isProd) {
    $baseUrl = "https://{$baseUrl}";
} else {
    $baseUrl = "http://localhost";
}

return ['settings' => [
    'env'                               => $env,
    'isProd'                            => $isProd,
    'displayErrorDetails'               => !$isProd,
    'determineRouteBeforeAppMiddleware' => false,
    'baseUrl'                           => $baseUrl,

    'gocardless'     => [
        'webhookSecret' => $_ENV['GOCARDLESS_WEBHOOK_SECRET'],
        'accessToken'   => $_ENV['GOCARDLESS_ACCESS_TOKEN'],
    ],
    'doctrine'       => [
        'dev_mode'   => !$isProd,
        'entityDir'  => APP_ROOT . '/src/Domain',
        'connection' => [
            'driver'   => $_ENV['DB_DRIVER'],
            'host'     => $_ENV['DB_HOST'],
            'port'     => $_ENV['DB_PORT'],
            'dbname'   => "iwgb-members{$dbSuffix}",
            'charset'  => $_ENV['DB_CHARSET'],
            'user'     => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ],
    ],
    'typeform'       => [
        'webhookSecret'   => $_ENV['TYPEFORM_WEBHOOK_SECRET'],
        'api'             => $_ENV['TYPEFORM_API_KEY'],
        'coreQuestionsId' => $_ENV['TYPEFORM_CORE_QUESTIONS_FORM_ID'],
    ],
    'airtable'       => [
        'key'      => $_ENV['AIRTABLE_API_KEY'],
        'base'     => $_ENV['AIRTABLE_BASE_ID'],
        'proxyKey' => $_ENV['AIRTABLE_PROXY_KEY'],
    ],
    'action-network' => [
        'token' => $_ENV['ACTION_NETWORK_API_TOKEN'],
    ],
    'api'            => [
        'token' => $_ENV['MEMBERS_API_TOKEN'],
    ],
    'sentry'         => [
        'dsn' => $_ENV['SENTRY_DSN'],
    ]
]];