<?php

namespace HyperfTest;

use Hyperf\Testing\TestCase as BaseTestCase;
use Hyperf\Testing\Concerns\MakesHttpRequests;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;

abstract class TestCase extends BaseTestCase
{
    use MakesHttpRequests;
    use RunTestsInCoroutine;
}
