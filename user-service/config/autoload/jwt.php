<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'secret' => env('JWT_SECRET'),
    // 可以给默认值，防止.env不存在的时候报错
    // 'secret' => env('JWT_SECRET', 'default-secret'),
    'ttl' => 3600,
    // ...其他配置
];
