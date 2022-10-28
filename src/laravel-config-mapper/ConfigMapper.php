<?php

namespace Skywarth\LaravelConfigMapper;


use InvalidArgumentException;

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
     * @return string
     */
    public function getFolderDelimiterCharacter(): string
    {
        return $this->configuration['folder_delimiter_character'];
    }

    /**
     * @return string
     */
    public function getWordDelimiterCharacter(): string
    {
        return $this->configuration['word_delimiter_character'];
    }





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
        $key=$this->camelToSnake($key);
        return preg_replace("/[^a-zA-Z0-9{$this->getFolderDelimiterCharacter()}]/", $this->getWordDelimiterCharacter(), $key);
    }

    //https://stackoverflow.com/a/40514305 thanks
    private function camelToSnake($string, $replaceWith = "#") {
        return strtolower(preg_replace(
            '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', $replaceWith, $string));
    }
}