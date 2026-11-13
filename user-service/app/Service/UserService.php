<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;
use App\Model\UserAddress;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
use Mall\Common\Middleware\AuthMiddleware;
use function Hyperf\Config\config;

class UserService
{
    public function register(array $data): bool
    {
        $model = new User();
        $model->mobile = $data['mobile'];
        $model->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $model->nickname = $data['nickname']??$model->mobile;
        $model->avatar = $data['avatar']??"";
        return $model->save();
    }

    public function login(string $mobile, string $password): bool|string
    {
        $userInfo = Db::table('users')->where('mobile', $mobile)->select(['id', 'password', 'mobile'])->first();
        if ($userInfo && password_verify($password, $userInfo->password)){
            return AuthMiddleware::sign([
                'uid' => $userInfo->id,
                'password' => $userInfo->password,
            ], config('jwt.secret', ''), config('jwt.ttl', 7200));
        }

        return false;
    }

    public function profile(User $userInfo, array $data)
    {
        return $userInfo->update($data);
    }

    public function addressCreate(array $data)
    {
        $uid = Context::get('uid', "");
        $userInfo = User::query()->find($uid);
        if (!$userInfo) return false;
        $data['user_id'] = $uid;
        try {
            if ($data['is_default'] == 1){
                $userInfo->addresses()->update(['is_default' => 0]);
            }
            $modelAddress = new UserAddress();
            $modelAddress->fill($data);
            $modelAddress->save();
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public function addressUpdate(array $data)
    {
        $userAddressInfo = UserAddress::query()->find($data['id']);
        if (!$userAddressInfo) return false;
        try {
            if ($data['is_default'] == 1){
                UserAddress::query()->where('user_id', $userAddressInfo->user_id)->update(['is_default' => 0]);
            }
            $userAddressInfo->update($data);
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public function queryBalance():bool | int
    {
        $uid = Context::get('uid', "");
        $user = User::query()->find($uid);
        if (!$user){return false;}

        $walletInfo = $user->wallet()->select('balance')->first();
        return $walletInfo->balance??0;
    }
}