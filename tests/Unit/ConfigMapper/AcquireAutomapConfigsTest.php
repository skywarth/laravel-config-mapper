<?php

namespace Skywarth\LaravelConfigMapper\Tests\Unit\ConfigMapper;







use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;

class AcquireAutomapConfigsTest extends AbstractServiceProviderTest
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

    protected function createConfigFiles(): array
    {
        $deleteTargets=collect();
        $deleteTargets->push($this->writeConfigFileToDirectory('mammals','panda','mammals.panda'));
        $deleteTargets->push($this->writeConfigFileToDirectory('mammals','dog','mammals.dog'));
        $deleteTargets->push($this->writeConfigFileToDirectory('non-mammals','penguin','non-mammals.penguin'));
        $deleteTargets->push($this->writeConfigFileToDirectory('mammals/room','elephant','mammals.room.elephant'));

        return $deleteTargets->toArray();
    }



    public function test_get_all_configs(){
        $configs=ConfigMapper::getAllConfigsArray();
        $this->assertIsArray($configs);
        $this->assertNotEmpty($configs);
    }

    public function test_get_automap_configs(){
        //First A of unit test 'Arrange' is done on getConfigArray()
        $configs=ConfigMapper::getAllConfigsArray();
        $automapConfigs=ConfigMapper::filterOutNonAutomapConfigs($configs);
        $normalizedAutomapConfigs=Utility::flatten($automapConfigs);

        $this->assertCount(2,$normalizedAutomapConfigs);//because two automaps defined in getConfigArray();
        $this->assertNotEmpty($automapConfigs['mammals']['room']['elephant']['permissions']['allowed_to_walk']);
        $this->assertNotEmpty($automapConfigs['non-mammals']['penguin']['slide']);
    }

    public function test_get_mapped_env_key(){
        //this unit test contains several cardinal sins

        //First A of unit test 'Arrange' is done on getConfigArray()

        //Deliberately altering it for comprehensive test
        Config::set('laravel-config-mapper.delimiters.folder_delimiter_character','.');
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character','.');
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character','');

        $mappedEnvKey=ConfigMapper::getMappedEnvKey('mammals.room.elephant.permissions.allowed_to_walk');

        $expected='MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWEDTOWALK';
        $this->assertEquals($expected,$mappedEnvKey);

    }


    public function test_get_mapped_config(){
        Config::set('laravel-config-mapper.delimiters.folder_delimiter_character','.');
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character','.');
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character','');

        $mappedEnvkey='MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWEDTOWALK';
        $expected='hello';
        Config::set($mappedEnvkey,$expected);


        $result=ConfigMapper::getMappedConfig('mammals.room.elephant.permissions.allowed_to_walk');
        $this->assertEquals($expected,$result);

    }


    public function test_get_mapped_env_key_out_of_bounds_exception()
    {
        Config::set('laravel-config-mapper.delimiters.folder_delimiter_character', '.');
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character', '.');
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character', '');

        $this->expectException(\OutOfBoundsException::class);

        $mappedEnvKey=ConfigMapper::getMappedEnvKey('some.random.config.that-doesnt.exist');
    }


}