<?php namespace GetCode\LaravelLogs\Models;
/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 19.10.2018
 * Time: 14:37
 */

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LogQueue extends Model
{
    public $guarded = [];

    const STATUS_PENDING = 0;
    const STATUS_FAILED = 1;
    const STATUS_COMPLETED = 2;

    public function __construct(array $attributes = [])
    {
        if (! isset($this->table)) {
            $this->setTable(config('getcode.laravel-logs.log_queues_table_name'));
        }

        $this->connection = config('getcode.laravel-logs.log_connection');

        parent::__construct($attributes);
    }

    public function setStatusPending()
    {
        $this->status = self::STATUS_PENDING;
    }

    public function setStatusFailed()
    {
        $this->status = self::STATUS_FAILED;
    }

    public function setStatusCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
    }

    public function findLastWithData($data)
    {
        $data = array_merge($data, [
            'status' => self::STATUS_PENDING
        ]);

        return $this
            ->where($data)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function setQueueStartTime()
    {
        $this->queue_start_time = Carbon::now()->toDateTimeString();
    }

    public function setQueueEndTime()
    {
        $this->queue_end_time = Carbon::now()->toDateTimeString();

        $this->setQueueExecuteTime();
    }

    protected function setQueueExecuteTime()
    {
        if (blank($this->queue_start_time) || blank($this->queue_end_time)) {
            return null;
        }

        $this->queue_execute_time = strtotime($this->queue_end_time) - strtotime($this->queue_start_time);
    }

    public function findByQueueId($queue_id)
    {
        return $this->where('queue_id', $queue_id)->first();
    }

}