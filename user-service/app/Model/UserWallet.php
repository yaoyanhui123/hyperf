<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $user_id 
 * @property int $balance 余额（分）
 * @property int $frozen 冻结（分）
 * @property int $points 积分
 * @property int $version 
 * @property \Carbon\Carbon $updated_at 
 */
class UserWallet extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_wallets';

    /**
     * The attributes that are mass assignable.
     * @var list<string>
     */
    protected array $fillable = ['user_id', 'balance', 'frozen', 'points', 'version'];

    /**
     * The attributes that should be cast to native types.
     * @var array<string, mixed>
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'balance' => 'integer', 'frozen' => 'integer', 'points' => 'integer', 'version' => 'integer'];
}
