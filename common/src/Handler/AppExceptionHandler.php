<?php

declare(strict_types=1);

namespace Mall\Common\Handler;

use Hyperf\Context\Context;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Mall\Common\AppResponse;
use Mall\Common\ErrorCode;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

/**
 * 兜底：未预期异常记 error 日志（带 trace_id），向前端只返回通用码，绝不返回堆栈。
 * 落点：/opt/www/mall/common/src/Handler/AppExceptionHandler.php
 * 必须注册在 exceptions.php 的最后。
 * 参数类型必须用 Psr ResponseInterface（原因见 BusinessExceptionHandler 的注释）
 */
class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(private ResponseInterface $response, private LoggerFactory $loggerFactory)
    {
    }

    public function handle(Throwable $throwable, PsrResponseInterface $response)
    {
        $this->loggerFactory->get('error')->error('unhandled', [
            'trace_id' => Context::get('trace_id'),
            'class' => get_class($throwable),
            'msg' => $throwable->getMessage(),
            'file' => $throwable->getFile() . ':' . $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
        ]);
        $this->stopPropagation();

        return $this->response->json(AppResponse::fail(ErrorCode::SERVER_ERROR, ErrorCode::getMessage(ErrorCode::SERVER_ERROR)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
