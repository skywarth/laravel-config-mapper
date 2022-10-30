<?php

namespace Skywarth\LaravelConfigMapper;

class Utility
{

    //https://stackoverflow.com/a/40514305 thanks
    public static function camelToSnake($string, $replaceWith = "#"):string {
        return strtolower(preg_replace(
            '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', $replaceWith, $string));
    }

    //https://stackoverflow.com/a/62807978 Thanks
    public static function flatten($array, $prefix = ''):array {
        $folderDelimiter='.';//intentionally independent from ConfigMapper's config
        $return = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, self::flatten($value, $prefix . $key . $folderDelimiter));
            } else {
                $return[$prefix . $key] = $value;
            }
        }
        return $return;
    }


}