<?php

namespace Mall\Common\Rule;


use Hyperf\Validation\Contract\Rule;

class Mobile implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (is_string($value)) return false;
        return true;
    }

    public function message(): string
    {
        return '手机号格式不正确';
    }
}