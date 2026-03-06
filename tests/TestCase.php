<?php

namespace NextMigrant\Sendy\Tests;

use NextMigrant\Sendy\SendyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SendyServiceProvider::class,
        ];
    }
}
