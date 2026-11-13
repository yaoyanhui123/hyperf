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
use Mall\Common\Handler\AppExceptionHandler;
use Mall\Common\Handler\BusinessExceptionHandler;
use Mall\Common\Handler\ValidationExceptionHandler;

return [
    'handler' => [
        'http' => [
            ValidationExceptionHandler::class,
            BusinessExceptionHandler::class,
            AppExceptionHandler::class,
        ],
    ],
];
