<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.wiki
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'http' => [
        // 网关是全站 trace 的起点，必须第一个挂
        Mall\Common\Middleware\TraceIdMiddleware::class,
        \Mall\Common\Middleware\AccessLogMiddleware::class,
    ],
];
