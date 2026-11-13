<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Validation\Middleware\ValidationMiddleware;
use \Mall\Common\Middleware\TraceIdMiddleware;
use \Mall\Common\Middleware\IdempotentMiddleware;
use \Mall\Common\Middleware\AccessLogMiddleware;

return [
    'http' => [
        TraceIdMiddleware::class,
        IdempotentMiddleware::class,
        AccessLogMiddleware::class,
        ValidationMiddleware::class,
    ],
];
