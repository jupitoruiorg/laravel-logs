<?php namespace GetCode\LaravelLogs\Providers;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 18.10.2018
 * Time: 14:21
 */

use GetCode\LaravelLogs\LaravelLogger;
use GetCode\LaravelLogs\Models\LogQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class LogServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishConfig();
        $this->publishMigrations();

        $this->registerLogQueues();

        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        $this->loadViewsFrom(__DIR__.'../../resources/views', 'laravel-logs');
        $this->publishes([
            __DIR__.'/../../resources/views' => base_path('resources/views/vendor/laravel-logs')
        ]);
    }


    public function register()
    {
        $this->app->bind(LaravelLogger::class);
        $this->bindIf('gc.laravel-logs.log.repo', 'GetCode\LaravelLogs\Repositories\Logs\LogQueueRepository');

        $this->bindIf('gc.logs.queue.mailer', 'GetCode\LaravelLogs\Mailer\QueueMailer');
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

    public static function determineLogQueueModel(): string
    {
        $logQueueModel = config('getcode.laravel-logs.log_queue_model') ?? LogQueue::class;
        if (! is_a($logQueueModel, LogQueue::class, true)
            || ! is_a($logQueueModel, Model::class, true)) {
            throw InvalidConfiguration::modelIsNotValid($logQueueModel);
        }
        return $logQueueModel;
    }
    public static function getLogQueueModelInstance(): Model
    {
        $activityModelClassName = self::determineLogQueueModel();
        return new $activityModelClassName();
    }

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

            if (
                config('getcode.laravel-logs.get_code_log_queues_fields') === true
                    &&
                method_exists($command, 'getFileName')
                    &&
                method_exists($command, 'getOption')
            ) {
                $data = array_merge($data, [
                    'file_name' => $command->getFileName(),
                    'pdf_view' => $command->getOption('pdf_view'),
                    'export_source' => $command->getOption('export_source'),
                    'caused_by' => $command->getOption('user_id'),
                ]);
            }

            laravel_log()
                ->useLog('queue')
                ->setData($data)
                ->asStart()
                ->log();

        });

        Queue::after(function (JobProcessed $event) {

            $data = [
                'queue_id' => $event->job->getJobId(),
                'connection_name' => $event->connectionName,
                'command_name' => array_get($event->job->payload(), 'displayName', ''),
            ];

            laravel_log()
                ->useLog('queue')
                ->setData($data)
                ->asCompleted()
                ->log();
        });

        Queue::failing(function (JobFailed $event) {

            $data = [
                'queue_id' => $event->job->getJobId(),
                'connection_name' => $event->connectionName,
                'command_name' => array_get($event->job->payload(), 'displayName', ''),
            ];

            laravel_log()
                ->useLog('queue')
                ->setData($data)
                ->asFailed()
                ->log();

        });
    }
}