<?php

// settings.php

$keys = require_once APP_ROOT . '/keys.php';

return [
    'gocardless' => [
        'webhookSecret' => $keys['gocardless']['webhook'],
    ],
    'sentry' => [
        'dsn' => $keys['sentry'],
    ],
];