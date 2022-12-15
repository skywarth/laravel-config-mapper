<?php

namespace Skywarth\LaravelConfigMapper\Tests\Feature\PublishMappedEnvKeys;

use Illuminate\Support\Facades\Config;
use Skywarth\LaravelConfigMapper\Utility;

abstract class AbstractPublishMappedEnvKeysTest extends \Skywarth\LaravelConfigMapper\Tests\TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->setConfigVariants();

    }

    abstract protected function getConfigArray():array;
    /*
     $standardConfig=[
            'laravel-config-mapper'=>[
                'delimiters'=>[
                    'folder_delimiter_character'=>'.',
                    'inside_config_delimiter_character'=>'.',
                    'word_delimiter_character'=>'_',
                ],
                'register_helpers'=>true
            ]

        ];
     */

    protected final function setConfigVariants(){
        $configArray=$this->getConfigArray();
        if(empty($configArray)){
            return;
        }
        $standardConfigFlat=Utility::flatten($configArray);

        foreach ($standardConfigFlat as $key=>$value){
            Config::set($key,$value);
        }
    }






}