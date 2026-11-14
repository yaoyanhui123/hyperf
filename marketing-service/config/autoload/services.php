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

    // Hyperf 只会自动注册 #[RpcService] 的 RPC 服务；
    // 纯 HTTP 服务靠 common 里的 HttpServiceRegisterListener 注册，所以这里留空
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
                'enable' => false,     // 走 HTTP 接口注册，不开 gRPC 通道
                'heartbeat' => 10,
            ],
        ],
    ],
];