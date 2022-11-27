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
        if(
            $configuration['folder_delimiter_character']===$configuration['word_delimiter_character']
            ||
            $configuration['inside_config_delimiter_character']===$configuration['word_delimiter_character']
        ){
            throw new InvalidArgumentException('folder_delimiter_character or inside_config_delimiter_character cannot be the same as word_delimiter_character !');
        }
        if(strlen($configuration['folder_delimiter_character'])!==1 || strlen($configuration['inside_config_delimiter_character'])!==1){
            throw new InvalidArgumentException('folder_delimiter_character and inside_config_delimiter_character configs has to be char ! (Single character string)');
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
     * Get the inside config delimiter character defined by the configuration.
     * Inside config delimiter character is used for separating arrays and keys INSIDE the config, descending in the config.
     *
     * @return string
     */
    public function getInsideConfigDelimiterCharacter(): string
    {
        return $this->configuration['inside_config_delimiter_character'];
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
        $configPathSeparated=$this->separateConfigPath($key);
        $foldersDelimiterApplied=$this->replaceFolderDelimiters($configPathSeparated['folder_only_path']);
        $insideConfigDelimiterApplied=$this->replaceInsideConfigDelimiters($configPathSeparated['inside_config_path']);
        $key=$foldersDelimiterApplied.$this->getInsideConfigDelimiterCharacter().$insideConfigDelimiterApplied;
        $key=$this->replaceWordDelimiters($key);
        return strtoupper($key);
    }


    private function separateConfigPath($key):array{
        //hold up this is kinda duplicate function, and even written worse. check getConfigFilePathFromConfigKeyString()
        $exploded=explode('.',$key);
        $configDir=config_path().'/';//every config must be under /config/
        $folderOnlyPath='';
        $insideConfigPath='';
        foreach ($exploded as $pathFragment){
            // careful, as of now of writing, File::exists returns true if the given path is FOLDER.
            // If this changes, we should also check for folder as well
            $folderOnlyPathFS=str_replace('.','/',$folderOnlyPath);//file system path
            $lookupTarget=$configDir.$folderOnlyPathFS.$pathFragment;
            $isFolderOrFile=File::exists($lookupTarget)
                || File::exists($lookupTarget.'.php');
            if($isFolderOrFile){
                $folderOnlyPath.=$pathFragment.'.';
            }else{
                $insideConfigPath.=$pathFragment.'.';
            }
        }
        $folderOnlyPath=substr($folderOnlyPath,0,-1);
        $insideConfigPath=substr($insideConfigPath,0,-1);
        return [
            'folder_only_path'=>$folderOnlyPath,
            'inside_config_path'=>$insideConfigPath
        ];
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