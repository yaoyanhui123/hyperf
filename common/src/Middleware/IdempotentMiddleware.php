<?php

declare(strict_types=1);

namespace Mall\Common\Middleware;

use Hyperf\Context\Context;
use Hyperf\Redis\Redis;
use Mall\Common\BusinessException;
use Mall\Common\ErrorCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * 幂等（防抖）：靠 Redis SET NX EX 挡住 30 秒内的重复提交。
 * 落点：/opt/www/mall/common/src/Middleware/IdempotentMiddleware.php
 * 它只是"防抖"，真正的不重复下单要靠唯一索引 + 状态机（第 7、8 章）。
 */
class IdempotentMiddleware implements MiddlewareInterface
{
    public function __construct(private Redis $redis)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = $request->getHeaderLine('x-idempotent-key');
        if ($key === '') {
            return $handler->handle($request);
        }

        $redisKey = 'idem:' . (int) Context::get('uid', 0) . ':' . $key;

        // SET key val NX EX 30 —— Redis 2.6.12 起就支持，你的 3.0.504 完全可用
        $ok = $this->redis->set($redisKey, '1', ['NX', 'EX' => 30]);
        if (! $ok) {
            throw new BusinessException(ErrorCode::TOO_MANY_REQUESTS, '请勿重复提交');
        }

        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            // 业务失败要释放锁，否则用户 30 秒内不能重试
            $this->redis->del($redisKey);
            throw $e;
        }
    }
}
