<?php

namespace Skywarth\LaravelConfigMapper\Tests\Feature\PublishMappedEnvKeys;







use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CommandExistsAndAvailableTest extends AbstractPublishMappedEnvKeys
{

    protected function getConfigArray(): array
    {
        return [

        ];
    }

    public function test_command_is_available_by_artisan_list(){

        $this->assertArrayHasKey('laravel-config-mapper:publish-env-keys',Artisan::all());
    }

    public function test_command_is_available_by_calling_it(){

        try{
           $this->artisan('laravel-config-mapper:publish-env-keys')
               ->expectsConfirmation('Is config paths and mapped env keys suitable ?','no')
               ->assertFailed();
        }catch (CommandNotFoundException $ex){
            $this->fail('Command is not registered');
        }

        $this->assertTrue(true);

    }


    protected function createConfigFiles(): array
    {
        return [];
    }
}