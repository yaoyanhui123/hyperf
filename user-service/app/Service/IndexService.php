<?php

namespace App\Service;

class IndexService
{

    public function user()
    {
//        return 'user';
        return $this->test();
    }

    private function test()
    {
        return 'test';

    }
}