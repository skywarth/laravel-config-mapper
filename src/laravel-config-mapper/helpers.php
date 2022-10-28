<?php


if (! function_exists('configMapped')) {
    /**
     * Get the path to the application folder.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Config\Repository
     */
    function configMapped($string)
    {

        if(config($string)!=='automap'){
            return config($string);
        }else{
            return \ConfigMapper::getMappedConfig($string);
        }
    }
}