<?php

declare(strict_types=1);

namespace Mall\Common\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Mall\Common\AppResponse;
use Mall\Common\BusinessException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

/**
 * 业务异常 → HTTP 200 + 业务码（前端按 code 分支，不用管 HTTP 状态）。
 * 落点：/opt/www/mall/common/src/Handler/BusinessExceptionHandler.php
 *
 * ⚠️ handle() 的第二个参数必须声明成 Psr\Http\Message\ResponseInterface。
 * 你这套 vendor 里父类 ExceptionHandler 的签名是 Swow\Psr7\Message\ResponsePlusInterface，
 * 如果写成 Hyperf\HttpServer\Contract\ResponseInterface，PHP 会直接 fatal：
 *   Declaration of ... must be compatible with Hyperf\ExceptionHandler\ExceptionHandler::handle()
 * （参数类型只允许"更宽"，不允许窄化；Psr ResponseInterface 是更宽的那个）
 */
class BusinessExceptionHandler extends ExceptionHandler
{
    public function __construct(private ResponseInterface $response)
    {
    }

    public function handle(Throwable $throwable, PsrResponseInterface $response)
    {
        /** @var BusinessException $throwable */
        $this->stopPropagation();

        // 用注入的响应工厂造响应（$response 参数是框架产物，只保证 PSR 接口）
        return $this->response->json(
            AppResponse::fail($throwable->bizCode, $throwable->getMessage(), $throwable->extra)
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof BusinessException;
    }
}
