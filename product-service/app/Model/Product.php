<?php

declare(strict_types=1);

namespace App\Model;



use Hyperf\Database\Model\SoftDeletes;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id 
 * @property string $spu_no SPU 编号
 * @property string $title 
 * @property string $subtitle 
 * @property int $category_id 类目
 * @property int $price 售价（分）
 * @property int $origin_price 原价（分）
 * @property int $stock 可售库存（冗余，真实库存在 inventory 服务）
 * @property int $sales 销售量
 * @property int $status 1上架 0下架
 * @property string $images 
 * @property string $attrs 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $deleted_at 
 */
class Product extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'products';

    /**
     * The attributes that are mass assignable.
     * @var list<string>
     */
    protected array $fillable = ['spu_no', 'title', 'subtitle', 'category_id', 'price', 'origin_price', 'stock', 'status', 'images', 'attrs'];

    protected array $hidden = ['deleted_at'];
    /**
     * The attributes that should be cast to native types.
     * @var array<string, mixed>
     */
    protected array $casts = ['id' => 'integer', 'category_id' => 'integer', 'price' => 'integer', 'origin_price' => 'integer', 'stock' => 'integer', 'sales' => 'integer', 'status' => 'integer', 'images' => 'json', 'attrs' => 'json'];
}
