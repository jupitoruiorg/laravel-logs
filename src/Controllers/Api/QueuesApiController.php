<?php namespace GetCode\LaravelLogs\Controllers\Api;
/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 2019-01-19
 * Time: 13:45
 */


use Illuminate\Support\Facades\App;

class QueuesApiController extends \Illuminate\Routing\Controller {

    /**
     * QueuesApiController constructor.
     */
    public function __construct() {

    }

    /**
     * Return the main view.
     *
     * @return \Illuminate\View\View
     */
    public function queuesInfo() {
        list(
            $log_queue_total,
            $log_queue_today,
            $log_queue_today_pending_much_time,
            $log_queue_total_failed,
            $log_queue_total_failed_percentage,
            $log_queue_today_failed,
            $log_queue_today_failed_percentage,
            $log_queue_total_completed,
            $log_queue_total_completed_percentage,
            $log_queue_today_completed,
            $log_queue_today_completed_percentage
            ) = array_values(app('gc.laravel-logs.log.repo')->getAllTotals());
        $log_queue_is_last_pending_much_time = app('gc.laravel-logs.log.repo')->checkIfLastPendingMuchTime();

        $status_text = null;

        if ($log_queue_is_last_pending_much_time === true) {
            $status_text = 'NOT ACTIVE (last queue is pending much time)';
        } elseif ($log_queue_today_failed > 0) {
            $status_text = 'ACTIVE (BUT today many failed queues)';
        } else {
            $status_text = 'ACTIVE';
        }

        return response()->json([
            'status_text' => $status_text,

            'total'                      => $log_queue_total,
            'today'                      => $log_queue_today,
            'pending_much_time_today'    => $log_queue_today_pending_much_time,
            'failed_total'               => $log_queue_total_failed,
            'failed_total_percentage'    => $log_queue_total_failed_percentage,
            'failed_today'               => $log_queue_today_failed,
            'failed_today_percentage'    => $log_queue_today_failed_percentage,
            'completed_total'            => $log_queue_total_completed,
            'completed_total_percentage' => $log_queue_total_completed_percentage,
            'completed_today'            => $log_queue_today_completed,
            'completed_today_percentage' => $log_queue_today_completed_percentage,

        ], 200);
    }

}
