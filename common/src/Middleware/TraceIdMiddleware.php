<?php

declare(strict_types=1);

namespace Mall\Common\Middleware;

use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * trace_id 贯穿全链路的第一块砖：上游传了就沿用，没有就生成。
 * 落点：/opt/www/mall/common/src/Middleware/TraceIdMiddleware.php
 */
class TraceIdMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $traceId = $request->getHeaderLine('x-trace-id') ?: bin2hex(random_bytes(8));

        // 3.2 的 Context::set 只有两个参数有意义：id / value
        // （第三个参数是 coroutineId，写成 3600 会把数据写进 3600 号协程，读的时候永远是 null）
        Context::set('trace_id', $traceId);

        $response = $handler->handle($request);

        return $response->withHeader('x-trace-id', $traceId);
    }
}
