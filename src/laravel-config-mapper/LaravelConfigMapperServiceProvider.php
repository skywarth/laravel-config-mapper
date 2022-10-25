<?php

namespace Skywarth\LaravelConfigMapper;

use Illuminate\Support\ServiceProvider;


class LaravelConfigMapperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $originalConfigFunc=function($str){
            config($str);
        };
        /*foreach (glob(app_path().'/Helpers/*.php') as $filename){
            require_once($filename);
        }*/

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {


    }
}
