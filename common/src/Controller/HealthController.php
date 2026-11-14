<?php

declare(strict_types=1);
/**
 * 每个服务都有的探活接口：GET /health
 * 放在 common 里是因为 7 个服务的这个接口逻辑完全一样（基础设施接口，不是业务接口）。
 *
 * 注意：注解扫描路径已经把 ../common/src 加进去了（第 4 章 4.0），
 * 所以这个类会被每个服务自动收集成路由，不需要在各自的 routes.php 里再写一遍。
 *
 * 落点：/opt/www/mall/common/src/Controller/HealthController.php
 */
namespace Mall\Common\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Mall\Common\AppResponse;

use function Hyperf\Support\env;

#[Controller]
class HealthController
{
    #[GetMapping(path: '/health')]
    public function health()
    {
        return AppResponse::ok([
            'service' => (string) env('SERVICE_NAME', 'unknown'),
            'port' => (int) env('HTTP_PORT', 0),
            'pid' => getmypid(),
            'time' => date('Y-m-d H:i:s'),
        ]);
    }
}
