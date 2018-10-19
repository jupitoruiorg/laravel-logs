<?php
/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 18.10.2018
 * Time: 14:21
 */
use GetCode\LaravelLogs\LaravelLogger;
if (! function_exists('laravel_log')) {
    function laravel_log(string $logName = null): LaravelLogger
    {
        $defaultLogName = config('getcode.laravel-logs.default_log_name');
        return app(LaravelLogger::class)
            ->useLog($logName ?? $defaultLogName);
    }
}