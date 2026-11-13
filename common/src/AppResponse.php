<?php

declare(strict_types=1);

namespace Mall\Common;

use Hyperf\Context\Context;

/**
 * 统一响应壳：7 个服务的所有接口都返回这个结构，网关聚合时不用逐个适配。
 * 落点：/opt/www/mall/common/src/AppResponse.php
 */
final class AppResponse
{
    public const OK = 0;

    public static function ok(mixed $data = null, string $msg = 'ok'): array
    {
        return ['code' => self::OK, 'msg' => $msg, 'data' => $data, 'trace_id' => self::traceId()];
    }

    public static function fail(int $code, string $msg = null, mixed $data = null): array
    {
        return ['code' => $code, 'msg' => $msg??ErrorCode::getMessage($code), 'data' => $data, 'trace_id' => self::traceId()];
    }

    public static function page(array $list, int $total, int $page, int $size): array
    {
        return self::ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);
    }

    private static function traceId(): string
    {
        // 注意：Context::set 的第三个参数在 3.2 是 coroutineId，不是 TTL，别传 3600
        return (string) Context::get('trace_id', '');
    }
}
