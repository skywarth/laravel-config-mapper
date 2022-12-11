<?php

namespace Skywarth\LaravelConfigMapper\Tests\Unit\ConfigMapper;

use Illuminate\Support\Facades\Config;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;

class ServiceProviderDontRegisterHelperTest extends AbstractServiceProviderTest
{

    protected function getConfigArray(): array
    {
        return [
            'laravel-config-mapper'=>[

                'register_helpers'=>false
            ]

        ];
    }

    public function test_config_merge(){
        $this->assertTrue(config('laravel-config-mapper.register_helpers')===false);
    }


    /* Canceled this one. I don't know how to actually unset a defined function.
    //Or how to clear global namespace from certain function
    public function test_helper_is_not_registered(){
        $helperFunctionExists=function_exists('configMapped');
        $this->assertNotTrue($helperFunctionExists);
    }
    */



}