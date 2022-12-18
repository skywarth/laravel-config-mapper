<?php

namespace Skywarth\LaravelConfigMapper\Tests\Feature\PublishMappedEnvKeys;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;

abstract class AbstractPublishMappedEnvKeys extends \Skywarth\LaravelConfigMapper\Tests\TestCase
{

    protected array $configFilesToBeDeletedLater=[];

    /**
     * @return array
     */
    public function getConfigFilesToBeDeletedLater(): array
    {
        return $this->configFilesToBeDeletedLater;
    }



    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->setConfigVariants();

        $this->configFilesToBeDeletedLater=$this->createConfigFiles();
        return;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->getConfigFilesToBeDeletedLater() as $configFilePath){
            if(!File::exists($configFilePath)){
                continue;
            }
            if(File::isDirectory($configFilePath)){
                File::deleteDirectory($configFilePath,false);
            }else{
                File::delete($configFilePath);
            }
        }
        // additional setup
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

    abstract protected function createConfigFiles():array;

    /**
     * @param string $relativePathDir Relative to config_path(), without leading and trailing '/'. E.g: "mammals/room/elephant"
     * @param string $filename Without file extension (.php)
     * @param string $fileContent
     * @return string
     */
    protected final function writeConfigFileToDirectory(string $relativePathDir, string $filename, string $configKey):string{

        $noDir=empty($relativePathDir);

        $targetConfigDir=(config_path().'/').(!$noDir?($relativePathDir.'/'):'');
        $relativeConfigFile=$filename.'.php';

        if(!$noDir && !File::isDirectory($targetConfigDir)){
            File::makeDirectory($targetConfigDir,0775,true);
        }
        File::put($targetConfigDir.$relativeConfigFile,'<?php return ' . var_export(config($configKey), true) . ';');
        $this->assertFileExists($targetConfigDir.$relativeConfigFile);

        return $noDir?($targetConfigDir.$relativeConfigFile):(config_path().'/'.explode('/',$relativePathDir)[0]);
    }






}