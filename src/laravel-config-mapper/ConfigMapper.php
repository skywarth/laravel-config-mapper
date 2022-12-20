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
        $this->validateConfiguration($configuration);
        $this->configuration = $configuration;
    }

    private function validateConfiguration(array $configuration){



        $folderDelimiterCharacter=$configuration['delimiters']['folder_delimiter_character'];
        $insideConfigDelimiterCharacter=$configuration['delimiters']['inside_config_delimiter_character'];
        $wordDelimiterCharacter=$configuration['delimiters']['word_delimiter_character'];

        if(
            $folderDelimiterCharacter===$wordDelimiterCharacter
            ||
            $insideConfigDelimiterCharacter===$wordDelimiterCharacter
        ){
            // I don't recall why I added this to begin with, but it doesn't seem to cause havoc, so I'm commenting it out for now
            //throw new InvalidArgumentException('folder_delimiter_character or inside_config_delimiter_character cannot be the same as word_delimiter_character !');
        }

        if(strlen($folderDelimiterCharacter)!==1 || strlen($insideConfigDelimiterCharacter)!==1){
            throw new InvalidArgumentException('folder_delimiter_character and inside_config_delimiter_character configs has to be char ! (Single character string)');
        }

        if(strlen($wordDelimiterCharacter)>1){
            throw new InvalidArgumentException("word_delimiter_character has to be a char or empty string ''. ");

        }

        if(
            !(
            $this->isValidEnvKeyCharacter($folderDelimiterCharacter) &&
            $this->isValidEnvKeyCharacter($insideConfigDelimiterCharacter) &&
            ($this->isValidEnvKeyCharacter($wordDelimiterCharacter) || strlen($wordDelimiterCharacter)===0)
            )
        ){
            throw new InvalidArgumentException("Invalid character is used for delimiter characters in the config. Adjust your config or revert back to default config for this package. ");
        }

    }

    private function isValidEnvKeyCharacter(string $char):bool{
        $dotEnvValidNonLetters=['_','.'];//Unbelievable they don't expand this

        return (in_array($char,$dotEnvValidNonLetters) || ctype_alnum($char));
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
        return $this->configuration['delimiters']['folder_delimiter_character'];
    }

    /**
     * Get the inside config delimiter character defined by the configuration.
     * Inside config delimiter character is used for separating arrays and keys INSIDE the config, descending in the config.
     *
     * @return string
     */
    public function getInsideConfigDelimiterCharacter(): string
    {
        return $this->configuration['delimiters']['inside_config_delimiter_character'];
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
        return $this->configuration['delimiters']['word_delimiter_character'];
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

        $configPathSeparated=$this->getConfigFilePathFromConfigKeyString($key);

        $foldersDelimiterApplied=$this->replaceFolderDelimiters($configPathSeparated['folder_only_config_key']);
        $insideConfigDelimiterApplied=$this->replaceInsideConfigDelimiters($configPathSeparated['inside_file_only_config_key']);
        $key=$foldersDelimiterApplied.$this->getInsideConfigDelimiterCharacter().$insideConfigDelimiterApplied;
        $key=$this->replaceWordDelimiters($key);
        return strtoupper($key);
    }

    private function replaceFolderDelimiters($key){
        return str_replace('.',$this->getFolderDelimiterCharacter(),$key);
    }

    private function replaceInsideConfigDelimiters($key){
        return str_replace('.',$this->getInsideConfigDelimiterCharacter(),$key);
    }

    private function replaceWordDelimiters($key){
        $key=Utility::camelToSnake($key);
        //how are '\\' working ??? Welp, as long as it does the job...
        //I would really appreciate it if some regex pro would provide a better alternative to the query below
        return preg_replace("/[^a-zA-Z0-9\\{$this->getFolderDelimiterCharacter()}\\{$this->getInsideConfigDelimiterCharacter()}]/", $this->getWordDelimiterCharacter(), $key);
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
        $lastKeyInFile=end($explodedConfigParts);
        $keyInFile='';
        $iteration=0;
        $path='';
        $popped=collect();
        do{
            $popped->push(array_pop($explodedConfigParts));
            $pathWithoutExtension=$configDir.implode('/',$explodedConfigParts);
            $path=$pathWithoutExtension.$configFileExtension;

            if($pathWithoutExtension===$configDir || $pathWithoutExtension===base_path()  || $iteration>100){
                throw new OutOfBoundsException("Couldn't locate config: last path ${pathWithoutExtension}, iteration ${iteration}");
            }
            $isConfigPathFile=File::exists($path);


            $iteration++;
        }while(!$isConfigPathFile);
        $popped=$popped->reverse();

        return [
            'file_path'=>$path,
            'config_key'=>$configKeyString,
            'folder_only_config_key'=>implode('.',$explodedConfigParts),//this is not used anywhere ?
            'inside_file_only_config_key'=>$popped->implode('.'),
            'last_key_in_file'=>$lastKeyInFile,

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