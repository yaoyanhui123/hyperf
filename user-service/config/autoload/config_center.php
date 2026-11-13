<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\ConfigCenter\Mode;
use Hyperf\ConfigNacos\Constants;
use Hyperf\ConfigNacos\NacosDriver;

use function Hyperf\Support\env;

return [
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', false),
    'driver' => env('CONFIG_CENTER_DRIVER', 'nacos'),
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'nacos' => [
            'driver' => NacosDriver::class,
            'merge_mode' => Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            'default_key' => 'nacos_config',
            'listener_config' => [
                // dataId, group, tenant, type, content
                'databases' => [
                    'tenant' => '', // corresponding with service.namespaceId
                    'data_id' => 'my-app',
                    'group' => 'DEFAULT_GROUP',
                ],
            ],
        ],
    ],
];
