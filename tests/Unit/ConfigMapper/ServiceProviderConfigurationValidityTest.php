<?php

namespace Skywarth\LaravelConfigMapper\Tests\Unit\ConfigMapper;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;

class ServiceProviderConfigurationValidityTest extends AbstractServiceProviderTest
{

    /*
       public function test_config_merge(){
          $this->assertTrue(
               config('laravel-config-mapper.delimiters.folder_delimiter_character')
               ===
               $this->getConfigArray()['laravel-config-mapper']['delimiters']['folder_delimiter_character']
           );

           $this->assertTrue(
               config('laravel-config-mapper.delimiters.inside_config_delimiter_character')
               ===
               $this->getConfigArray()['laravel-config-mapper']['delimiters']['inside_config_delimiter_character']
           );

           $this->assertTrue(
               config('laravel-config-mapper.delimiters.word_delimiter_character')
               ===
               $this->getConfigArray()['laravel-config-mapper']['delimiters']['word_delimiter_character']
           );
    }
*/


    /* VALIDS START */

    public function test_standard_config_is_valid(){

        $configuration=ConfigMapper::getConfiguration();
        $this->assertIsArray($configuration);
    }

    public function test_alternative_config_is_valid(){
        Config::set('laravel-config-mapper.delimiters.folder_delimiter_character','_');
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character','.');
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character','');
        $configuration=ConfigMapper::getConfiguration();
        $this->assertIsArray($configuration);
    }



    /* VALIDS END */


    /* INVALIDS START */
    public function test_folder_delimiter_non_char_string(){
        Config::set('laravel-config-mapper.delimiters.folder_delimiter_character','___');
        $this->expectException(\InvalidArgumentException::class);
        ConfigMapper::getConfiguration();
    }

    public function test_inside_config_delimiter_non_char_string(){
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character','___');
        $this->expectException(\InvalidArgumentException::class);
        ConfigMapper::getConfiguration();
    }

    public function test_word_delimiter_multi_char_string(){
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character','___');
        $this->expectException(\InvalidArgumentException::class);
        ConfigMapper::getConfiguration();
    }


    public function test_folder_delimiter_invalid_env_char(){
        Config::set('laravel-config-mapper.delimiters.folder_delimiter_character','#');
        $this->expectException(\InvalidArgumentException::class);
        ConfigMapper::getConfiguration();
    }

    public function test_inside_config_delimiter_invalid_env_char(){
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character','?');
        $this->expectException(\InvalidArgumentException::class);
        ConfigMapper::getConfiguration();
    }

    public function test_word_delimiter_invalid_env_char(){
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character','/');
        $this->expectException(\InvalidArgumentException::class);
        ConfigMapper::getConfiguration();
    }

    public function test_multiple_delimiters_invalid(){
        Config::set('laravel-config-mapper.delimiters.inside_config_delimiter_character','/');
        Config::set('laravel-config-mapper.delimiters.word_delimiter_character','##');
        $this->expectException(\InvalidArgumentException::class);
        ConfigMapper::getConfiguration();
    }
    /* INVALIDS END */

}