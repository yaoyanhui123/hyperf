<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class UserAddressRequest extends FormRequest
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
            'id' => 'required',
            'receiver' => 'required|max:20',
            'mobile' => 'required|mobile',
            'province' => 'required|max:20',
            'city' => 'required|max:20',
            'district' => 'required|max:20',
            'detail' => 'required|max:200',
            'is_default' => 'required|in:0,1',
        ];
    }
    public function messages(): array
    {
        return [
            'id.required' => '地址ID不能为空',
            'receiver.required' => '收货人不能为空',
            'mobile.required' => '手机号不能为空',
            'province.required' => '省份不能为空',
            'city.required' => '城市不能为空',
            'district.required' => '区域不能为空',
            'detail.required' => '详细地址不能为空',
            'is_default.required' => '是否默认不能为空',
            'is_default.in' => '是否默认格式错误',
        ];
    }

    protected array $scenes = [
        'create' => ['receiver', 'mobile', 'province', 'city', 'district', 'detail', 'is_default'],
        'update' => ['id', 'receiver', 'mobile', 'province', 'city', 'district', 'detail', 'is_default'],
    ];
}
