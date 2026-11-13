<?php

declare(strict_types=1);

namespace Mall\Common\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\ValidationException;
use Mall\Common\AppResponse;
use Mall\Common\ErrorCode;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

/**
 * 参数校验失败 → code 10001 + 首个错误信息 + 全部字段错误。
 * 落点：/opt/www/mall/common/src/Handler/ValidationExceptionHandler.php
 * 参数类型必须用 Psr ResponseInterface（原因见 BusinessExceptionHandler 的注释）
 */
class ValidationExceptionHandler extends ExceptionHandler
{
    public function __construct(private ResponseInterface $response)
    {
    }

    public function handle(Throwable $throwable, PsrResponseInterface $response)
    {
        /** @var ValidationException $throwable */
        $this->stopPropagation();
        $errors = $throwable->validator->errors();
        $first = $errors->first();

        return $this->response->json(
            AppResponse::fail(ErrorCode::PARAM_ERROR, (string) $first, $errors->toArray())
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}
