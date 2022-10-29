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

    }
}
