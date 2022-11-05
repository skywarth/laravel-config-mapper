<?php

namespace Skywarth\LaravelConfigMapper;


use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use OutOfBoundsException;

class ConfigMapper
{
    private array $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        if($configuration['folder_delimiter_character']===$configuration['word_delimiter_character']){
            throw new InvalidArgumentException('Folder delimiter and word delimiter cannot be the same');
        }
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Get the folder delimiter character defined by the configuration
     * Folder delimiter character is used for separating sub-dirs in mapped env key
     *
     * @return string
     */
    public function getFolderDelimiterCharacter(): string
    {
        return $this->configuration['folder_delimiter_character'];
    }

    /**
     * Get the word delimiter character defined by the configuration
     * Word delimiter character is used for separating words in folder/file/key name.
     * camelCase, kebab-case and snake_case will be split with defined word delimiter char
     *
     * @return string
     */
    public function getWordDelimiterCharacter(): string
    {
        return $this->configuration['word_delimiter_character'];
    }


    /**
     * @param $key
     * @return mixed
     *
     */
    public function getMappedConfig($key){
        return env($this->getMappedEnvKey($key));

    }

    public function getMappedEnvKey($key):string{
        $key=$this->replaceFolderDelimiters($key);
        $key=$this->replaceWordDelimiters($key);
        return strtoupper($key);
    }

    private function replaceFolderDelimiters($key){
        return str_replace('.',$this->getFolderDelimiterCharacter(),$key);
    }

    private function replaceWordDelimiters($key){
        $key=Utility::camelToSnake($key);
        return preg_replace("/[^a-zA-Z0-9{$this->getFolderDelimiterCharacter()}]/", $this->getWordDelimiterCharacter(), $key);
    }


    public function getAllConfigsArray():array{
        $config=config();//this gives us all configs as Illuminate\Config\Repository. It includes subdirs too
        //alternative: Storage::allFiles(config_path());
        return $config->all();
    }

    public function getConfigFilePathFromConfigKeyString(string $configKeyString):array{
        $configFileExtension='.php';
        $configDir=config_path().'/';//every config must be under /config/
        $explodedConfigParts=explode('.',$configKeyString);
        $keyInFile=end($explodedConfigParts);
        $iteration=0;
        $path='';
        do{
            array_pop($explodedConfigParts);
            $path=$configDir.implode('/',$explodedConfigParts).$configFileExtension;
            if($path===base_path() || $iteration>100){
                throw new OutOfBoundsException("Couldn't locate config");
            }
            $isConfigPathFile=File::exists($path);


            $iteration++;
        }while(!$isConfigPathFile);

        return [
            'file_path'=>$path,
            'config_string'=>implode('.',$explodedConfigParts),
            'key_in_file'=>$keyInFile
        ];
    }

    //https://stackoverflow.com/a/29612139 thanks
    public function filterOutNonAutomapConfigs(&$configs) :array{
        //CAREFUL, notice the ampersand. Updates in place. Unless you are calling from outside
        foreach ( $configs as $key => $item ) {
            is_array ( $item ) && $configs[$key] = $this->filterOutNonAutomapConfigs( $item );
            if ((!is_array($configs[$key]) && $configs[$key]!=='automap')||empty($configs[$key])){
                unset($configs[$key]);
            }

        }
        return $configs;
    }


}