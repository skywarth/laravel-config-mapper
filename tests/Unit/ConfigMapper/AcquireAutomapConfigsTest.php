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

        $targetConfigDir=config_path().'/mammals/room';
        $relativeConfigFile='elephant.php';

        //MAYBE REFACTOR. YOU DEFINITELY GONNA NEED IT
        //TODO: yeah refactor for sure, below function uses the almost exact same
        File::makeDirectory($targetConfigDir,0775,true);
        File::put($targetConfigDir.'/'.$relativeConfigFile,
            "
            <?php
#asdad
return [
    'enabled'=>'automap',
    'permissions'=>[
        'allowed_to_walk'=>'automap',
        'allowed_to_sleep'=>env('MAMMALS_ROOM_ELEPHANT_PERMISSIONS_ALLOWED_TO_SLEEP',1)
    ]
];
            "
        );
        $expected='MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWEDTOWALK';
        $this->assertFileExists($targetConfigDir.'/'.$relativeConfigFile);
        $mappedEnvKey=ConfigMapper::getMappedEnvKey('mammals.room.elephant.permissions.allowed_to_walk');
        $this->assertEquals($expected,$mappedEnvKey);

        File::deleteDirectory(config_path().'/mammals',false);

        //TODO: we should unset/revert the config::set's that are assigned in the beginning. Is it really necessary ? Not sure.
    }


    public function test_get_mapped_config(){
        Config::set('laravel-config-mapper.delimiters.folder_delimiter_character','.');
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character','.');
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character','');

        $mappedEnvkey='MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWEDTOWALK';
        $expected='hello';
        Config::set($mappedEnvkey,$expected);


        $targetConfigDir=config_path().'/mammals/room';
        $relativeConfigFile='elephant.php';

        //MAYBE REFACTOR. YOU DEFINITELY GONNA NEED IT
        File::makeDirectory($targetConfigDir,0775,true);
        File::put($targetConfigDir.'/'.$relativeConfigFile,
            "
            <?php
#asdad
return [
    'enabled'=>'automap',
    'permissions'=>[
        'allowed_to_walk'=>'automap',
        'allowed_to_sleep'=>env('MAMMALS_ROOM_ELEPHANT_PERMISSIONS_ALLOWED_TO_SLEEP',1)
    ]
];
            "
        );
        $this->assertFileExists($targetConfigDir.'/'.$relativeConfigFile);

        $result=ConfigMapper::getMappedConfig('mammals.room.elephant.permissions.allowed_to_walk');
        $this->assertEquals($expected,$result);

        File::deleteDirectory(config_path().'/mammals',false);

    }



}