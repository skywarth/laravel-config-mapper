<?php

namespace Skywarth\LaravelConfigMapper\Tests\Feature\PublishMappedEnvKeys;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class JustOutputTest extends AbstractPublishMappedEnvKeys
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
            ],
            'blasphemous-root-config'=>[
                'you-got-a-licence-for-that'=>'automap'
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
        $deleteTargets->push($this->writeConfigFileToDirectory('','blasphemous-root-config','blasphemous-root-config'));

        return $deleteTargets->toArray();
    }


    public function test_just_output(){

        $applyChoices=[
            1=>"Just output the mapped env keys, I'll copy them myself",
            2=>"Add mapped env keys to file",
            3=>"Update configs to replace 'automap' values with corresponding env keys, then add mapped keys to file",
        ];

        $configs=ConfigMapper::getAllConfigsArray();

        $automapConfigs=ConfigMapper::filterOutNonAutomapConfigs($configs);
        $normalizedAutomapConfigs=Utility::flatten($automapConfigs);
        foreach ($normalizedAutomapConfigs as $configPath=>$value){
            $mappedEnvKey=ConfigMapper::getMappedEnvKey($configPath);
            $normalizedAutomapConfigs[$configPath]=$mappedEnvKey;
        }

        $outputString="#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE"."\n";

        $outputString.="MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=\nNON_MAMMALS.PENGUIN.SLIDE=\nBLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT=";
        $outputString.="\n"."#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE";;


        $this->artisan('laravel-config-mapper:publish-env-keys')
            ->expectsOutput('Discovering config files')
            ->expectsOutput('Filtering non-automap configs out')
            ->expectsOutput('Automap configs below:')
            ->expectsTable(
                ['Config Path','Automap Env key'],
                [//order is extremely important
                    ['config_path'=>'mammals.room.elephant.permissions.allowed_to_walk','mapped_env_key'=>'MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK'],
                    ['config_path'=>'non-mammals.penguin.slide','mapped_env_key'=>'NON_MAMMALS.PENGUIN.SLIDE'],
                    ['config_path'=>'blasphemous-root-config.you-got-a-licence-for-that','mapped_env_key'=>'BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT'],
                ]
            )
            ->expectsConfirmation('Is config paths and mapped env keys suitable ?','yes')
            ->expectsChoice('How would you like to proceed ?',$applyChoices[1],$applyChoices)
            ->expectsOutput('--------------COPY BELOW--------------')
            ->expectsOutput($outputString)
            ->expectsOutput('--------------COPY ABOVE--------------')
            ->assertSuccessful();
    }


}