<?php

declare(strict_types=1);

namespace Mall\Common\Middleware;

use App\Model\User;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Mall\Common\BusinessException;
use Mall\Common\ErrorCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 鉴权：手写 HMAC-SHA256 的 JWT（三段式），不引第三方包。
 * 落点：/opt/www/mall/common/src/Middleware/AuthMiddleware.php
 * 注意：login/register 这类公开接口不要挂它（见 4.6）。
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $auth = $request->getHeaderLine('authorization');
        $token = str_starts_with($auth, 'Bearer ') ? substr($auth, 7) : '';
        if ($token === '') {
            throw new BusinessException(ErrorCode::UNAUTHORIZED);
        }

        $payload = self::verify($token, (string) $this->config->get('jwt.secret', ''));
        if ($payload === null) {
            throw new BusinessException(ErrorCode::UNAUTHORIZED);
        }

        $userInfo = User::query()->find((int) ($payload['uid'] ?? 0));
        if (!$userInfo || $userInfo->status !== 1){
            throw new BusinessException(ErrorCode::UNAUTHORIZED);
        }

        // 用户身份放 Context：协程隔离，后续任何层都能取，不会串号
        Context::set('uid', (int) ($payload['uid'] ?? 0));
        Context::set('jwt_payload', $payload);

        return $handler->handle($request);
    }

    public static function sign(array $payload, string $secret, int $ttl = 7200): string
    {
        $payload += ['iat' => time(), 'exp' => time() + $ttl];
        $b64 = self::b64(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $sig = self::b64(hash_hmac('sha256', $b64, $secret, true));

        return "{$b64}.{$sig}";
    }

    public static function verify(string $token, string $secret): ?array
    {
        [$b64, $sig] = array_pad(explode('.', $token), 2, '');
        if ($b64 === '' || $sig === '' || $secret === '') {
            return null;
        }
        $expect = self::b64(hash_hmac('sha256', $b64, $secret, true));
        // 必须 hash_equals，防时序攻击（== 会在第一个不同字节就返回，能被测出签名）
        if (! hash_equals($expect, $sig)) {
            return null;
        }
        $payload = json_decode(self::unb64($b64), true);
        if (! is_array($payload) || (int) ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return $payload;
    }

    private static function b64(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }

    private static function unb64(string $s): string
    {
        return base64_decode(strtr($s, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($s)) % 4));
    }
}
