<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class UserRequest extends FormRequest
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
            'mobile' => 'required|mobile',
            'password' => 'required|min:6|alpha_dash:ascii',
            'nickname' => 'max:20',
            'avatar' => 'url',
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => '手机号不能为空',
            'mobile.mobile' => '手机号格式不正确11',
            'mobile.unique' => '手机号已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码长度不能少于6位',
            'password.alpha_dash' => '密码格式不正确',
            'nickname.max' => '昵称长度不能超过20个字符',
            'avatar.url' => '头像格式不正确',
        ];

    }

    protected array $scenes = [
        'profile' => ['nickname', 'avatar'],
    ];
}
