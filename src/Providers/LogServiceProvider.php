<?php namespace GetCode\LaravelLogs\Providers;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 18.10.2018
 * Time: 14:21
 */

use GetCode\LaravelLogs\LaravelLogger;
use GetCode\LaravelLogs\Mailer\Views\QueueMailCompleted;
use GetCode\LaravelLogs\Mailer\Views\QueueMailFailed;
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
    }

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

            $command_name = array_get($event->job->payload(), 'displayName', '');
            if (in_array($command_name, [QueueMailFailed::class, QueueMailCompleted::class])) {
                return false;
            }

            $data = [
                'queue_id'        => $event->job->getJobId(),
                'connection_name' => $event->connectionName,
                'command_name'    => $command_name,
            ];

            if (
                config('getcode.laravel-logs.get_code_log_queues_fields') === true
                    &&
                method_exists($command, 'getFileName')
                    &&
                method_exists($command, 'getOption')
            ) {
                $data = array_merge($data, [
                    'file_name'     => $command->getFileName(),
                    'pdf_view'      => $command->getOption('pdf_view'),
                    'export_source' => $command->getOption('export_source'),
                    'caused_by'     => $command->getOption('user_id'),
                ]);
            }

            laravel_log()
                ->useLog('queue')
                ->setData($data)
                ->asStart()
                ->log();

        });

        Queue::after(function (JobProcessed $event) {

            $command_name = array_get($event->job->payload(), 'displayName', '');
            if (in_array($command_name, [QueueMailFailed::class, QueueMailCompleted::class])) {
                return false;
            }

            $data = [
                'queue_id'        => $event->job->getJobId(),
                'connection_name' => $event->connectionName,
                'command_name'    => $command_name,
            ];

            laravel_log()
                ->useLog('queue')
                ->setData($data)
                ->asCompleted()
                ->log();
        });

        Queue::failing(function (JobFailed $event) {

            $command_name = array_get($event->job->payload(), 'displayName', '');
            if (in_array($command_name, [QueueMailFailed::class, QueueMailCompleted::class])) {
                return false;
            }

            $data = [
                'queue_id'        => $event->job->getJobId(),
                'connection_name' => $event->connectionName,
                'command_name'    => $command_name,
            ];

            laravel_log()
                ->useLog('queue')
                ->setData($data)
                ->asFailed()
                ->log();

        });
    }
}