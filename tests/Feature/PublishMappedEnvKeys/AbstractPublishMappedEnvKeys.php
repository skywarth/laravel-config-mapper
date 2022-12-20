<?php

namespace Skywarth\LaravelConfigMapper\Tests\Feature\PublishMappedEnvKeys;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;

abstract class AbstractPublishMappedEnvKeys extends \Skywarth\LaravelConfigMapper\Tests\TestCase
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

}