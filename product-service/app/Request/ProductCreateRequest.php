<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class ProductCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:128',
            'price' => 'required|integer|min:1',
            'category_id' => 'required|integer|min:1',
            'stock' => 'required|integer|min:0|max:999999',
            'images' => 'array|max:9',
            'images.*' => 'string|url',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '标题不能为空',
            'title.string' => '标题必须是字符串',
            'title.max' => '标题不能超过128个字符',
            'price.required' => '价格不能为空',
            'price.integer' => '价格必须是整数（单位：分）',
            'price.min' => '价格不能小于1',
            'category_id.required' => '分类不能为空',
            'category_id.integer' => '分类必须是整数',
            'category_id.min' => '分类不能小于1',
            'stock.required' => '库存不能为空',
            'stock.integer' => '库存必须是整数',
            'stock.min' => '库存不能小于0',
            'stock.max' => '库存不能超过999999',
            'images.array' => '图片必须是数组',
            'images.max' => '图片不能超过9张',
            'images.*.string' => '图片必须是字符串',
            'images.*.url' => '图片必须是有效的URL',
        ];

    }
}
