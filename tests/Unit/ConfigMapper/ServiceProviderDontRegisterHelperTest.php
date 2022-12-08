<?php

namespace Skywarth\LaravelConfigMapper\Tests\Unit\ConfigMapper;

use Illuminate\Support\Facades\Config;
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


    public function test_helper_is_not_registered(){
        $helperFunctionExists=function_exists('configMapped');
        $this->assertNotTrue($helperFunctionExists);
    }

}