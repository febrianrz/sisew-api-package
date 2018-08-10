<?php

namespace Situsewa\Cores;

use Illuminate\Support\ServiceProvider;

class SitusewaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__."app/";
        include __DIR__."src/";
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app['cores'] = $this->app->share(function($app){
        //     return new Cores;
        // });
        $this->app->register('Situsewa/Cores/SitusewaServiceProvider');
    }
}
