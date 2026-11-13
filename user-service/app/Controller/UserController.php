<?php

namespace App\Controller;


use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

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
    public function register()
    {

    }

}