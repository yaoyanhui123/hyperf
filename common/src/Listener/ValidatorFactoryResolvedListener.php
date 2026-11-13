<?php

namespace Mall\Common\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;

#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [ValidatorFactoryResolved::class];
    }

    public function process(object $event): void
    {
        /** @var ValidatorFactoryInterface $factory */
        $factory = $event->validatorFactory;

        // 第四个参数 $validator 不能丢
        $factory->extend('mobile', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) return false;
            return preg_match('/^1[3-9]\d{9}$/', $value) === 1;
        });

        $factory->replacer('mobile', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, $message);
        });
    }
}