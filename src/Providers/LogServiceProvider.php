<?php namespace GetCode\LaravelLogs\Providers;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 18.10.2018
 * Time: 14:21
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class LogServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishConfig();
        $this->publishMigrations();

        $this->registerLogQueues();

    }


    public function register()
    {

    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/getcode.laravel-logs.php' => config_path('getcode.laravel-logs.php'),
        ], 'config');
        $this->mergeConfigFrom(__DIR__.'/../../config/getcode.laravel-logs.php', 'getcode.laravel-logs');
    }

    protected function publishMigrations()
    {
        $this->loadMigrationsFrom( __DIR__.'/../../migrations');
        //$this->publishQueueLogMigrations();
    }

    /*protected function publishQueueLogMigrations()
    {
        if (! class_exists('CreateGcLogQueuesTable')) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../../migrations/create_gc_log_queues_table.php.stub' => database_path("/migrations/{$timestamp}_create_gc_log_queues_table.php"),
            ], 'migrations');
        }

        if (! class_exists('AddGetCodeFieldsToGcLogQueuesTable')) {
            $timestamp = date('Y_m_d_His', time() + 1);
            $this->publishes([
                __DIR__.'/../../migrations/add_getcode_fields_to_gc_log_queues_table.php.stub' => database_path("/migrations/{$timestamp}_add_getcode_fields_to_gc_log_queues_table.php"),
            ], 'migrations');
        }
    }*/

    protected function registerLogQueues()
    {
        if (config('getcode.laravel-logs.log_queues_enabled') === false) {
            return;
        }

        Queue::before(function (JobProcessing $event) {
            $command = unserialize($event->job->payload()['data']['command']);

            $data = [
                'queue_id' => $event->job->getJobId(),
                'connection_name' => $event->connectionName,
                'command_name' => array_get($event->job->payload(), 'displayName', ''),
            ];

            if (config('getcode.laravel-logs.get_code_log_queues_fields') === true) {
                $data = array_merge($data, [
                    'file_name' => $command->getFileName(),
                    'pdf_view' => $command->getOption('pdf_view'),
                    'export_source' => $command->getOption('export_source'),
                    'user_id' => $command->getOption('user_id'),
                ]);
            }

        });

        Queue::after(function (JobProcessed $event) {
        });

        Queue::failing(function (JobFailed $event) {

        });
    }
}