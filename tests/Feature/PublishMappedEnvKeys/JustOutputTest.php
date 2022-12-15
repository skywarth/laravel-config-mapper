<?php

namespace Skywarth\LaravelConfigMapper\Tests\Feature\PublishMappedEnvKeys;







use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class JustOutputTest extends AbstractPublishMappedEnvKeysTest
{

    protected function getConfigArray(): array
    {
        return [
            'mammals'=>[
                'panda'=>[

                ],
                'dog'=>[

                ],
                'room'=>[
                    'elephant'=>[
                        'enabled'=>true,
                        'permissions'=>[
                            'allowed_to_walk'=>'automap',
                            'allowed_to_sleep'=>true,
                        ]
                    ]
                ]
            ],
            'non-mammals'=>[
                'penguin'=>[
                    'slide'=>'automap'
                ]
            ]

        ];
    }

    public function test_command_is_available_by_artisan_list(){

        $this->assertArrayHasKey('laravel-config-mapper:publish-env-keys',Artisan::all());
    }

    public function test_command_is_available_by_calling_it(){

        try{
            $this->artisan('laravel-config-mapper:publish-env-keys');
        }catch (CommandNotFoundException $ex){
            $this->fail('Command is not registered');
        }

        $this->assertTrue(true);

    }



}