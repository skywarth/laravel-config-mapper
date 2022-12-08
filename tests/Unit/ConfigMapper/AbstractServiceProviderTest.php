<?php

namespace Skywarth\LaravelConfigMapper\Tests\Unit\ConfigMapper;

use Illuminate\Support\Facades\Config;
use Skywarth\LaravelConfigMapper\Utility;

abstract class AbstractServiceProviderTest extends \Skywarth\LaravelConfigMapper\Tests\TestCase
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
        $standardConfigFlat=Utility::flatten($this->getConfigArray());

        foreach ($standardConfigFlat as $key=>$value){
            Config::set($key,$value);
        }
    }



    public function test_config_merge(){

    }


}