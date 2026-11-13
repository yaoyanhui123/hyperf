<?php

namespace App\Protocol;

use Hyperf\Contract\OnPacketInterface;

class UdpHandler implements OnPacketInterface
{

    /**
     * @inheritDoc
     */
    public function onPacket($server, $data, $clientInfo): void
    {
        // UDP 没有 fd，只能靠 address + port 回给对方
        $server->sendto(
            $clientInfo['address'],
            $clientInfo['port'],
            'pong:' . $data
        );
    }
}