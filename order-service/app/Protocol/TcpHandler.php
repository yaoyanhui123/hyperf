<?php

namespace App\Protocol;

use Hyperf\Contract\OnReceiveInterface;
use Psr\Log\LoggerInterface;

class TcpHandler implements OnReceiveInterface
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        var_dump('body 字节长度', strlen($data));
        var_dump('body hex', bin2hex($data));
        var_dump('body raw', $data);
        $length = unpack('N', substr($data, 0, 4))[1];
        $body = substr($data, 4);
        var_dump($body);
        $this->logger->info("tco recv", ['fd' => $fd, 'length' => $length, 'body' => $body]);

        $req  = json_decode($body, true) ?: [];
        $resp = [
            'code' => 0,
            'echo' => $req['msg'] ?? '',
            'time' => date('c'),
        ];

        $payload = json_encode($resp, JSON_UNESCAPED_UNICODE);
        // 回包也必须带长度头，否则客户端那边同样会粘包
        $server->send($fd, pack('N', strlen($payload)) . $payload);
    }

    public function onConnect($server, int $fd, int $reactorId): void
    {
        $this->logger->info("tcp connect", ['fd' => $fd]);
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        // 连接断开，清理
        $this->logger->info("tcp close", ['fd' => $fd]);
    }
}