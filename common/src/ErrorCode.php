<?php

declare(strict_types=1);

namespace Mall\Common;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 错误码集中管理（所有服务共用一份，避免"用户服务返回 10002、商品服务返回 401"）。
 * 落点：/opt/www/mall/common/src/ErrorCode.php
 *
 * 码段：1xxxx 通用/参数  2xxxx 交易  3xxxx 支付  4xxxx 营销  5xxxx 内部
 * 纪律：上线后只增不改语义。
 */
#[Constants]
class ErrorCode extends AbstractConstants
{
    /** @Message("ok") */
    public const OK = 0;

    /** @Message("参数错误") */
    public const PARAM_ERROR = 10001;

    /** @Message("未登录或登录过期") */
    public const UNAUTHORIZED = 10002;

    /** @Message("无权限") */
    public const FORBIDDEN = 10003;

    /** @Message("请求太频繁") */
    public const TOO_MANY_REQUESTS = 10004;

    /** @Message("资源不存在") */
    public const NOT_FOUND = 10005;

    /** @Message("业务繁忙，请重试") */
    public const FALLBACK = 10006;

    /** @Message("服务器开小差了") */
    public const SERVER_ERROR = 500;

    /** @Message("库存不足") */
    public const STOCK_SHORTAGE = 20001;

    /** @Message("订单状态不允许该操作") */
    public const ORDER_STATE_INVALID = 20002;

    /** @Message("支付失败") */
    public const PAY_FAILED = 30001;
}
