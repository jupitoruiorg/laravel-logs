<?php
/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 2019-01-19
 * Time: 13:42
 */

Route::group([
    'prefix' => 'gc-logs/api/',
    'as'        => 'gc-logs.api.'
], function () {

    Route::get('queues/info',
        [ 'as' => 'queues.info', 'uses' => 'GetCode\LaravelLogs\Controllers\Api\QueuesApiController@queuesInfo' ]);
});