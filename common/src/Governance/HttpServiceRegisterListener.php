<?php

declare(strict_types=1);
/**
 * 把「HTTP 服务」注册进 Nacos。
 *
 * 为什么需要这个类：
 * Hyperf 自带的 RegisterServiceListener 只注册 ServiceManager 里的条目，
 * 而 ServiceManager 只由 #[RpcService] 注解填充 —— 也就是「纯 HTTP 服务」默认不会出现在注册中心。
 * 但网关要做 REST 转发，必须能在 Nacos 里找到 HTTP 端口，所以这里补一个注册器。
 *
 * 它复用官方 NacosDriver（Hyperf\ServiceGovernanceNacos\NacosDriver）：
 *   ->register($name, $host, $port, $metadata)  内部会 POST /nacos/v1/ns/instance，
 *   并且自己起了心跳协程（每 heartbeat 秒 PUT /nacos/v1/ns/instance/beat），
 *   所以不用我们手写心跳。ephemeral=true 时进程挂掉 15~30 秒会被 Nacos 自动摘除。
 *
 * 落点：/opt/www/mall/common/src/Governance/HttpServiceRegisterListener.php
 */
namespace Mall\Common\Governance;

use Hyperf\Contract\IPReaderInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\ServiceGovernance\DriverManager;
use Throwable;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * ⚠️ Hyperf 3.2 里 `env()` / `config()` 不是全局函数，分别定义在
 * Hyperf\Support 和 Hyperf\Config 命名空间下，必须 `use function ...` 才能用，
 * 否则运行时报 Call to undefined function ...\config()（实测踩过，服务直接起不来）。
 */

class HttpServiceRegisterListener implements ListenerInterface
{
    public function __construct(
        private DriverManager $driverManager,
        private IPReaderInterface $ipReader,
        private StdoutLoggerInterface $logger
    ) {
    }

    public function listen(): array
    {
        // 只在主进程启动时注册一次；不要挂到 WorkerStart，否则 4 个 Worker 各注册一遍
        return [MainWorkerStart::class];
    }

    public function process(object $event): void
    {
        if (! (bool) config('services.enable.register', true)) {
            return;
        }

        $name = (string) env('SERVICE_NAME', '');
        if ($name === '') {
            $this->logger->warning('[governance] SERVICE_NAME 为空，跳过注册（检查 .env）');
            return;
        }

        $port = (int) env('HTTP_PORT', 9501);
        // 0.0.0.0 不能注册进注册中心（消费端连不上），统一换成容器/本机真实 IP
        $host = $this->ipReader->read();

        // driver 名固定为 nacos；如果你想换 consul，把这里和 services.php 一起改
        $driver = $this->driverManager->get('nacos');
        if ($driver === null) {
            $this->logger->warning('[governance] 没有 nacos driver，检查 config/autoload/services.php');
            return;
        }

        // metadata 会原样出现在 Nacos 控制台的「实例详情」里，后面网关/消费端按它过滤。
        // ⚠️ Nacos 的 metadata 是 Map<String,String>：值只能是字符串，
        //    写成 ['methods' => ['health']] 会被拒：metadata format incorrect:{...}（实测踩过）
        $metadata = [
            'protocol' => 'http',
            'path' => '/',
            'methods' => 'health',
            'app' => $name,
        ];

        try {
            $driver->register($name, $host, $port, $metadata);
            $this->logger->info(sprintf('[governance] registered %s -> %s:%d', $name, $host, $port));
        } catch (Throwable $e) {
            // 注册失败不能让服务起不来：本地开发 Nacos 没开也能跑业务
            $this->logger->error(sprintf('[governance] register %s failed: %s', $name, $e->getMessage()));
        }
    }
}
