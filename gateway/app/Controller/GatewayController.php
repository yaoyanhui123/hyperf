<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Context\Context;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\ServiceGovernance\DriverManager;
use Psr\Http\Message\ResponseInterface as PsrResponse;

/**
 * 落点：/opt/www/mall/gateway/app/Controller/GatewayController.php
 *
 * 一条路由吃掉所有 /api/{service}/{action}：
 *   GET /api/product/health  ->  从 Nacos 找到 mall-product 的实例，转发到 http://IP:9512/health
 */
#[Controller(prefix: '/api')]
class GatewayController
{
    /** 每个 Worker 进程一份：服务名 => ['nodes' => [...], 'expire_at' => int] */
    private array $nodeCache = [];

    /** 本地缓存秒数：太短会打爆 Nacos，太长摘除实例后感知慢。生产建议 10~30 秒 */
    private const NODE_CACHE_TTL = 10;

    public function __construct(
        private DriverManager $driverManager,
        private ClientFactory $clientFactory,
        private ResponseInterface $response
    ) {
    }

    /**
     * ⚠️ FastRoute 的硬规则：#[RequestMapping] 的 path 以 '/' 开头时，Controller 的 prefix 会被忽略。
     * 所以这里写 '{service}/{action:.*}'（不带前导斜杠），最终路由才是 '/api/{service}/{action:.*}'。
     * 写成 '/{service}/{action:.*}' 会得到 '/{service}/{action:.*}'，把整个站点的两段式路由全吃掉。
     */
    #[RequestMapping(path: '{service}/{action:.*}', methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])]
    public function dispatch(string $service, string $action, RequestInterface $request): PsrResponse
    {
        // 1) 查实例（带本地缓存）
        $serviceName = str_starts_with($service, 'mall-') ? $service : 'mall-' . $service;
        $nodes = $this->getNodes($serviceName);
        if ($nodes === []) {
            // 注意：Hyperf 的 Response::json($data) 只有一个参数，第二个参数不是状态码，
            // 要改状态必须链式 withStatus()（写成 json($data, 503) 不会生效，实测）
            return $this->response->json([
                'code' => 10005,
                'msg' => "服务 {$serviceName} 无可用实例",
                'data' => null,
                'trace_id' => (string) Context::get('trace_id', ''),
            ])->withStatus(503);
        }

        // 2) 选一个实例（随机；生产可换加权 / 一致性哈希 / 按 uid 灰度）
        $node = $nodes[array_rand($nodes)];

        // 3) 拼下游 URL：保留 query string，路径原样透传
        $query = $request->getUri()->getQuery();
        $url = sprintf(
            'http://%s:%d/%s%s',
            $node['host'],
            $node['port'],
            ltrim($action, '/'),
            $query === '' ? '' : '?' . $query
        );

        $client = $this->clientFactory->create([
            'timeout' => 3.0,
            'connect_timeout' => 0.5,
            'http_errors' => false,
        ]);

        try {
            $downstream = $client->request($request->getMethod(), $url, [
                'headers' => [
                    'content-type' => $request->getHeaderLine('content-type') ?: 'application/json',
                    'authorization' => $request->getHeaderLine('authorization'),
                    // trace 透传：下游 AppResponse 会读 Context，网关这边生成的 trace_id 必须带下去
                    'x-trace-id' => (string) Context::get('trace_id', ''),
                    'x-gateway' => $serviceName,
                ],
                'body' => (string) $request->getBody(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'code' => 10006,
                'msg' => "下游 {$serviceName} 调用失败: " . $e->getMessage(),
                'data' => null,
                'trace_id' => (string) Context::get('trace_id', ''),
            ])->withStatus(502);
        }

        return $this->response
            ->withStatus($downstream->getStatusCode())
            ->withHeader('content-type', $downstream->getHeaderLine('content-type') ?: 'application/json')
            ->withHeader('x-upstream', $node['host'] . ':' . $node['port'])
            ->withBody(new SwooleStream((string) $downstream->getBody()));
    }

    /**
     * 从 Nacos 取节点。DriverInterface::getNodes(string $uri, string $name, array $metadata)
     * uri/metadata 对 nacos driver 没用到（它只按 name + 配置里的 group/namespace 查），
     * 但签名要求传，所以给空值。
     */
    private function getNodes(string $serviceName): array
    {
        $now = time();
        $cached = $this->nodeCache[$serviceName] ?? null;
        if ($cached !== null && $cached['expire_at'] > $now) {
            return $cached['nodes'];
        }

        $driver = $this->driverManager->get('nacos');
        $nodes = $driver === null ? [] : $driver->getNodes('', $serviceName, []);

        $this->nodeCache[$serviceName] = [
            'nodes' => $nodes,
            'expire_at' => $now + self::NODE_CACHE_TTL,
        ];

        return $nodes;
    }
}
