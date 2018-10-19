<?php namespace GetCode\LaravelLogs\Repositories\Logs;
use GetCode\LaravelLogs\Models\LogQueue;
use GetCode\LaravelLogs\Traits\RepositoryTrait;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 19.10.2018
 * Time: 16:41
 */


class LogQueueRepository implements LogQueueRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Data handler.
     */
    protected $data;

    /**
     * The Eloquent model.
     *
     * @var string
     */
    protected $model;


    /**
     * LogQueueRepository constructor.
     */
    public function __construct() {
        $this->setModel(LogQueue::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->createModel()->get();
    }


    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        return $this->createModel()->find($id);
    }

    public function getTotal()
    {
        return $this->createModel()->count();
    }

    public function getTotalPending()
    {
        return $this->createModel()
            ->where('status', LogQueue::STATUS_PENDING)
            ->count();
    }

    public function getTotalFailed()
    {
        return $this->createModel()
            ->where('status', LogQueue::STATUS_FAILED)
            ->count();
    }

    public function getTotalCompleted()
    {
        return $this->createModel()
            ->where('status', LogQueue::STATUS_COMPLETED)
            ->count();
    }

    public function getTotalPendingALotOfTime()
    {
        return $this->createModel()
            ->where('status', LogQueue::STATUS_PENDING)
            ->whereRaw('queue_start_time < (NOW() - INTERVAL 5 MINUTE)')
            ->count();
    }

    public function getAllTotals()
    {
        return $this->createModel()
            ->select(
                DB::raw('count(id) as total'),
                DB::raw('SUM(if (DATE(created_at) = CURDATE(), 1, 0)) as today'),
                DB::raw('SUM(if (status = 0 and DATE(created_at) = CURDATE() and queue_start_time < (NOW() - INTERVAL 5 MINUTE), 1, 0)) as today_pending_much_time'),
                DB::raw('SUM(if (status = 1, 1, 0)) as total_failed'),
                DB::raw('concat(round(( SUM(if (status = 1, 1, 0))/count(id) * 100 ),2), \'%\') AS total_failed_percentage'),
                DB::raw('SUM(if (status = 1 and DATE(created_at) = CURDATE(), 1, 0)) as today_failed'),
                DB::raw('concat(round(( SUM(if (status = 1 and DATE(created_at) = CURDATE(), 1, 0))/count(id) * 100 ),2), \'%\') AS today_failed_percentage'),
                DB::raw('SUM(if (status = 2, 1, 0)) as total_completed'),
                DB::raw('concat(round(( SUM(if (status = 2, 1, 0))/count(id) * 100 ),2), \'%\') AS total_completed_percentage'),
                DB::raw('SUM(if (status = 2 and DATE(created_at) = CURDATE(), 1, 0)) as today_completed'),
                DB::raw('concat(round(( SUM(if (status = 2 and DATE(created_at) = CURDATE(), 1, 0))/count(id) * 100 ),2), \'%\') AS today_completed_percentage')

            )
            ->first()
            ->toArray();
    }

    public function checkIfLastPendingMuchTime()
    {
        $log = $this->createModel()
            ->select(
                'status',
                DB::raw('IF(queue_start_time < (NOW() - INTERVAL 5 MINUTE), true, false) as is_much_time')
            )
            ->orderBy('created_at', 'desc')
            ->first();

        if ($log->status === LogQueue::STATUS_PENDING && (int) $log->is_much_time === 1) {
            return true;
        }

        return false;
    }

}
