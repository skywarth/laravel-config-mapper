<?php

namespace Skywarth\LaravelConfigMapper\Tests;


use Skywarth\LaravelConfigMapper\LaravelConfigMapperServiceProvider;
use Skywarth\LaravelConfigMapper\Utility;

class TestCase extends \Orchestra\Testbench\TestCase{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelConfigMapperServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}