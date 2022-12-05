<?php

namespace Skywarth\LaravelConfigMapper;

use Illuminate\Support\ServiceProvider;
use Skywarth\LaravelConfigMapper\Console\PublishMappedEnvKeys;


class LaravelConfigMapperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'laravel-config-mapper');
        $this->app->bind('ConfigMapper', function($app) {
            return new ConfigMapper(config('laravel-config-mapper'));
        });




    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('laravel-config-mapper.php'),
            ], 'config');

            $this->commands([
                PublishMappedEnvKeys::class,
            ]);
        }


        $this->helperRegistration();//could be moved to register() I suppose

    }


    protected function helperRegistration(){
        //Previously, helper file registration was in composer.json->autoload->files.
        //But I opted for doing it by require_once, because it'll be registered by a condition
        /*
           "autoload": {
                "psr-4": {
                    "Skywarth\\LaravelConfigMapper\\": "src/laravel-config-mapper"
                },
                "files": [
                    "src/laravel-config-mapper/helpers.php"
                ]
           },
         */
        if(config('laravel-config-mapper.register_helpers')===true){
            $helperFilePath=__DIR__ . '/helpers.php';
            require_once($helperFilePath);
        }

    }
}
