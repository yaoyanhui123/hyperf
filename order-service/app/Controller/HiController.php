<?php

namespace App\Controller;

use Grpc\HiReply;
use Grpc\HiUser;

class HiController
{
    public function sayHello(HiUser $user)
    {
        $message = new HiReply();
        $message->setMessage("Hello World");
        $message->setUser($user);
        return $message;
    }

    public function add(\Grpc\AddRequest $req): \Grpc\AddReply
    {
        $reply = new \Grpc\AddReply();
        $reply->setSum($req->getA() + $req->getB());
        return $reply;
    }
}