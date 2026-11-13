<?php

declare(strict_types=1);

namespace Mall\Common;

/**
 * 业务异常（可预期）：控制器里 throw，由 BusinessExceptionHandler 统一转成 {code,msg,trace_id}。
 * 落点：/opt/www/mall/common/src/BusinessException.php
 */
class BusinessException extends \RuntimeException
{
    public function __construct(
        public readonly int $bizCode = ErrorCode::PARAM_ERROR,
        string $message = '',
        public readonly mixed $extra = null
    ) {
        parent::__construct($message !== '' ? $message : ErrorCode::getMessage($bizCode));
    }

    public static function stockShortage(int $skuId): self
    {
        return new self(ErrorCode::STOCK_SHORTAGE, "库存不足 sku={$skuId}", ['sku_id' => $skuId]);
    }

    public static function unauthorized(string $message = ''): self
    {
        return new self(ErrorCode::UNAUTHORIZED, $message);
    }
}
