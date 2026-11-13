<?php

declare(strict_types=1);

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
            // 共享包（服务目录的上一级 common/src）也要扫，否则常量注解拿不到
            BASE_PATH . '/../common/src',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
    ],
];
