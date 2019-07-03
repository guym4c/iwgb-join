<?php

// settings.php

$keys = require_once APP_ROOT . '/keys.php';

return [
    'sentry' => [
        'dsn' => $keys['sentry'],
    ],
];