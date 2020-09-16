<?php

namespace Iwgb\Join\Middleware;

use Tuupola\Middleware\CorsMiddleware;

class Cors {

    public static function withOptions() {
        return new CorsMiddleware([
            'origin' => ['*'],
            'methods' => ['GET', 'POST', 'OPTIONS'],
            'credentials' => true,
            'headers.expose' => [
                'Content-Length',
                'Content-Range',
            ],
            'headers.allow' => [
                'Authorization',
                'Content-Type',
                'DNT',
                'User-Agent',
                'X-Requested-With',
                'If-Modified-Since',
                'Cache-Control',
                'Range',
            ],
        ]);
    }
}