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
test('example', function () {
    expect(true)->toBeTrue();
});

test('加法正常', function () {
    $a = 1;
    $b = 2;
    expect($a + $b)->toBe(3);
});

//test('get 接口返回200', function () {
//    $resp = $this->get('/api/hello');
//    $resp->assertOk();
//    $resp->assertJson(['code' => 0]);
//});