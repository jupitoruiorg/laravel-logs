<?php
/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 18.10.2018
 * Time: 14:23
 */

return [
    /*
     * If set to false, no queues logs will be saved to the database.
     */
    'log_queues_enabled' => env('LARAVEL_LOG_QUEUES_ENABLED', false),

    'get_code_log_queues_fields' => env('GC_LARAVEL_LOG_QUEUES_FIELDS', false),

    'log_queues_table_name' => env('LARAVEL_LOG_QUEUES_TABLE_NAME', 'gc_log_queues'),

    'log_queue_model' => \GetCode\LaravelLogs\Models\LogQueue::class,

    'default_log_name' => '',

];