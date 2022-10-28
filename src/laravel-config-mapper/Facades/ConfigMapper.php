<?php

namespace Skywarth\LaravelConfigMapper\Facades;

use Illuminate\Support\Facades\Facade;


class ConfigMapper extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'ConfigMapper';
    }

}