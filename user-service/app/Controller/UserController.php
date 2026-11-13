<?php

namespace App\Controller;


use App\Request\RegisterRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mall\Common\AppResponse;

#[Controller(prefix: '/user')]
class UserController
{

    #[PutMapping(path: 'profile')]
    public function profile(RequestInterface $request)
    {
        
    }

    #[PostMapping(path: 'login')]
    public function login(RequestInterface $request)
    {

    }

    #[PostMapping(path: 'register')]
    public function register(RegisterRequest $request)
    {
        $request->validated();
        $param = $request->post();
        return AppResponse::ok($param);

    }

}