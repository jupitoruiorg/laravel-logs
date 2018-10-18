<?php namespace GetCode\LaravelLogs\Providers;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 18.10.2018
 * Time: 14:21
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/getcode.laravel-logs.php' => config_path('getcode.laravel-logs.php'),
        ], 'config');
        $this->mergeConfigFrom(__DIR__.'/../config/getcode.laravel-logs.php', 'getcode.laravel-logs');
    }


    public function register()
    {

    }
}