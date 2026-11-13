<?php

declare(strict_types=1);

namespace Mall\Common\Middleware;

use Hyperf\Context\Context;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * 访问日志：一个请求一行，带 trace_id / uid / 状态 / 耗时。
 * 落点：/opt/www/mall/common/src/Middleware/AccessLogMiddleware.php
 * 全局挂，且要排在 TraceIdMiddleware 之后，否则 trace_id 还是空的。
 */
class AccessLogMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerFactory $factory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);
        $status = 500;

        try {
            $response = $handler->handle($request);
            $status = $response->getStatusCode();

            return $response;
        } catch (Throwable $e) {
            $status = 500;
            throw $e;
        } finally {
            $this->factory->get('access')->info('access', [
                'trace_id' => (string) Context::get('trace_id', ''),
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
                'uid' => (int) Context::get('uid', 0),
                'status' => $status,
                'cost_ms' => round((microtime(true) - $start) * 1000, 2),
                'ip' => $request->getHeaderLine('x-forwarded-for') ?: ($request->getServerParams()['remote_addr'] ?? ''),
            ]);
        }
    }
}
