<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property string $mobile 手机号
 * @property string $password password_hash
 * @property string $nickname 昵称
 * @property string $avatar 头像
 * @property int $status 1正常 0禁用
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class User extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'users';

    /**
     * The attributes that are mass assignable.
     * @var list<string>
     */
    protected array $fillable = ['mobile', 'nickname', 'avatar', 'status'];

    /**
     * The attributes that should be cast to native types.
     * @var array<string, mixed>
     */
    protected array $casts = ['id' => 'integer', 'status' => 'integer'];

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'id');
    }
    
    public function wallet()
    {
        return $this->hasOne(UserWallet::class, 'user_id', 'id');
    }
}
