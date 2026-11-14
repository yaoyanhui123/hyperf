<?php
declare(strict_types=1);

use function Hyperf\Support\env;

return [
    // 两个开关：注册自己 / 发现别人
    'enable' => [
        'discovery' => true,
        'register' => true,
    ],

    // 我作为消费方要调用的服务（第 6 章填）
    'consumers' => [],

    // 我作为提供方额外要注册的服务（用注解就够了，这里一般留空）
    'providers' => [],

    'drivers' => [
        'nacos' => [
            'host' => env('NACOS_HOST', 'host.docker.internal'),
            'port' => (int) env('NACOS_PORT', 8848),
            'username' => null,
            'password' => null,
            'group_name' => env('NACOS_GROUP', 'DEFAULT_GROUP'),
            'namespace_id' => env('NACOS_NAMESPACE', 'mall'),
            'heartbeat' => 5,          // 临时实例心跳间隔（秒）
            'ephemeral' => true,       // 临时实例：进程挂了自动摘除
            'cluster' => 'DEFAULT',
            'grpc' => [
                'enable' => false,     // 用 HTTP 接口注册，不开 gRPC 通道
                'heartbeat' => 10,
            ],
        ],
    ],
];