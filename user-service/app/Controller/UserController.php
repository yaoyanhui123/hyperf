<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use App\Request\RegisterRequest;
use App\Request\UserAddressRequest;
use App\Request\UserRequest;
use App\Service\UserService;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Annotation\Scene;
use Mall\Common\AppResponse;
use Mall\Common\ErrorCode;
use Mall\Common\Middleware\AuthMiddleware;

#[Controller(prefix: '/user')]
class UserController
{

    #[Inject]
    protected UserService $userService;

    #[PutMapping(path: 'profile')]
    #[Middleware(AuthMiddleware::class)]
    #[Scene(scene: 'profile')]
    public function profile(UserRequest $request)
    {
        $data = $request->validated();
        if (empty($data)){
            return AppResponse::fail(code: ErrorCode::PARAM_ERROR);
        }
        $uid = Context::get('uid', "");
        if (empty($uid)){
            return AppResponse::fail(code: ErrorCode::UNAUTHORIZED);
        }
        $userInfo = User::query()->find($uid);
        if (!$userInfo){
            return AppResponse::fail(code: ErrorCode::SERVER_ERROR, msg: '用户不存在');
        }
        $res = $this->userService->profile($userInfo, $data);
        if ($res === false){
            return AppResponse::fail(code: ErrorCode::SERVER_ERROR, msg: '修改失败');
        }
        return AppResponse::ok(msg: '修改成功');
    }

    #[PostMapping(path: 'address')]
    #[Middleware(AuthMiddleware::class)]
    #[Scene(scene: 'create')]
    public function address_create(UserAddressRequest $request)
    {
        $data = $request->validated();
        if (empty($data)){
            return AppResponse::fail(code: ErrorCode::PARAM_ERROR);
        }
        $res = $this->userService->addressCreate($data);
        if ($res === false){
            return AppResponse::fail(code: ErrorCode::SERVER_ERROR, msg: '新增失败');
        }
        return AppResponse::ok(msg: '新增成功');
    }

    #[PutMapping(path: 'address')]
    #[Middleware(AuthMiddleware::class)]
    #[Scene(scene: 'update')]
    public function address_update(UserAddressRequest $request)
    {
        $data = $request->validated();
        if (empty($data)){
            return AppResponse::fail(code: ErrorCode::PARAM_ERROR);
        }
        $res = $this->userService->addressUpdate($data);
        if ($res === false){
            return AppResponse::fail(code: ErrorCode::SERVER_ERROR, msg: '修改失败');
        }
        return AppResponse::ok(msg: '修改成功');
    }

    #[PostMapping(path: 'login')]
    public function login(RequestInterface $request)
    {
        $mobile = $request->post('mobile', "");
        $password = $request->post('password', "");
        $res = $this->userService->login($mobile, $password);
        if ($res === false){
            return AppResponse::fail(code: ErrorCode::SERVER_ERROR, msg: '登录失败');
        }
        return AppResponse::ok(data: [
            'token' => $res
        ], msg: '登录成功');
    }

    #[PostMapping(path: 'register')]
    #[Scene(scene:'register')]
    public function register(RegisterRequest $request)
    {
        $request->validated();
        $postData = $request->post();
        $res = $this->userService->register($postData);
        if ($res){
            return AppResponse::ok(msg: '注册成功');
        }else{
            return AppResponse::fail(code:ErrorCode::SERVER_ERROR, msg: '注册失败');
        }

    }

    #[GetMapping(path: 'balance')]
    #[Middleware(AuthMiddleware::class)]
    public function queryBalance()
    {
        $res = $this->userService->queryBalance();
        if ($res === false){
            return AppResponse::fail(code: ErrorCode::SERVER_ERROR, msg: '查询失败');
        }
        return AppResponse::ok(data: [
            'balance' => $res
        ], msg: '查询成功');
    }

}