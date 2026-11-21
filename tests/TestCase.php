<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests;

use Ahmaruff\HtmxTable\HtmxTableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HtmxTableServiceProvider::class,
        ];
    }
}
