<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use Grpc\AddRequest;
use Grpc\HiReply;
use Grpc\HiUser;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function hello()
    {
        // 这个client是协程安全的，可以复用
        $client = new \App\Grpc\HiClient('127.0.0.1:9502', [
            'credentials' => null,
        ]);

        $request = new HiUser();
        $request->setName('hyperf');
        $request->setSex(1);

        // 拿到 call 对象
        $call = $client->sayHello($request);
        // wait() 阻塞等待，拿到 HiReply 消息对象
        /** @var \Grpc\HiReply $reply */
        [$reply, $status] = $call->wait();
        var_dump($status);

        if ($reply === null) {
            return [
                'code' => $status->code,
                'details' => $status->details,
            ];
        }

        return [
            'msg' => $reply->getMessage(),
            'name' => $reply->getUser()->getName(),
        ];
    }
    public function add()
    {
        // 这个client是协程安全的，可以复用
        $client = new \App\Grpc\HiClient('127.0.0.1:9502', [
            'credentials' => null,
        ]);

        $request = new AddRequest();
        $request->setA(1);
        $request->setB(2);

        // 拿到 call 对象
        $call = $client->add($request);
        // wait() 阻塞等待，拿到 HiReply 消息对象
        /** @var \Grpc\AddReply $reply */
        [$reply, $status] = $call->wait();

        if ($reply === null) {
            return [
                'code' => $status->code,
                'details' => $status->details,
            ];
        }

        return [
            'sum' => $reply->getSum(),
        ];
    }

}
