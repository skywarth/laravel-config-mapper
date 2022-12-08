<?php

namespace Skywarth\LaravelConfigMapper\Tests;


use Illuminate\Support\Facades\Config;
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
        parent::getEnvironmentSetUp($app);
        //Config::set();
        // perform environment setup
    }
}