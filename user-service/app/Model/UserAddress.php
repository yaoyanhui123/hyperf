<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $user_id 
 * @property string $receiver 
 * @property string $mobile 
 * @property string $province 
 * @property string $city 
 * @property string $district 
 * @property string $detail 
 * @property int $is_default 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class UserAddress extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_addresses';

    /**
     * The attributes that are mass assignable.
     * @var list<string>
     */
    protected array $fillable = ['user_id', 'receiver', 'mobile', 'province', 'city', 'district', 'detail', 'is_default'];

    /**
     * The attributes that should be cast to native types.
     * @var array<string, mixed>
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'is_default' => 'integer'];
}
