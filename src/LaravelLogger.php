<?php namespace GetCode\LaravelLogs;
use GetCode\LaravelLogs\Providers\LogServiceProvider;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 19.10.2018
 * Time: 14:25
 */

class LaravelLogger {

    protected $log_name;

    protected $log_status = false;

    protected $log_model = null;

    protected $log_data = [];
    
    protected $proccess_status;

    public function __construct()
    {
        $this->checkLogStatus();
        $this->setLogModel();
    }

    public function useLog($log_name = '')
    {
        $this->log_name = $log_name;

        return $this;
    }

    protected function getLogName()
    {
        return $this->log_name;
    }

    protected function checkLogStatus()
    {
        $this->log_status = false;

        switch ($this->getLogName()) {
            case 'queue':
            default:
                $this->log_status = config('getcode.laravel-logs.log_queues_enabled');
                break;
        }
    }

    protected function setLogModel()
    {
        switch ($this->getLogName()) {
            case 'queue':
            default:
                $this->log_model = LogServiceProvider::getLogQueueModelInstance();
                break;
        }
    }

    protected function getLogModel()
    {
        return $this->log_model;
    }

    protected function isLogDisabled()
    {
        return !$this->log_status;
    }

    protected function isLogModelExists()
    {
        return !is_null($this->log_model);
    }

    protected function isLogModelNotExists()
    {
        return !$this->isLogModelExists();
    }

    public function setData($data)
    {
        $this->log_data = $data;

        return $this;
    }

    protected function getData()
    {
        return $this->log_data;
    }

    public function asStart()
    {
        $this->proccess_status = 'start';

        return $this;
    }

    public function asFailed()
    {
        $this->proccess_status = 'failed';

        return $this;
    }

    public function asCompleted()
    {
        $this->proccess_status = 'completed';

        return $this;
    }

    protected function getProccessStatus()
    {
        return $this->proccess_status;
    }

    protected function isProccessStatusStart()
    {
        return $this->getProccessStatus() === 'start';
    }

    protected function isProccessStatusFailed()
    {
        return $this->getProccessStatus() === 'failed';
    }

    protected function isProccessStatusCompleted()
    {
        return $this->getProccessStatus() === 'completed';
    }

    protected function isEmptyData()
    {
        return count($this->getData()) === 0;
    }

    public function log()
    {
        if ($this->isLogDisabled()) {
            return;
        }

        if ($this->isLogModelNotExists()) {
            return;
        }

        if ($this->isEmptyData()) {
            return;
        }

        $model = null;


        switch ($this->getLogName()) {
            case 'queue':
            default:
                $model = $this->logQueues();
                break;
        }

        return $model;
    }

    protected function logQueues()
    {
        $model = $this->getLogModel();

        if ($this->isProccessStatusStart()) {

            $model->fill($this->getData());
            $model->setStatusPending();
            $model->setQueueStartTime();

        } elseif($this->isProccessStatusFailed()) {
            if (isset($this->getData()['queue_id']) && filled($this->getData()['queue_id'])) {
                $model = $model->findByQueueId($this->getData()['queue_id']);
            } elseif(isset($this->getData()['queue_id']) && blank($this->getData()['queue_id'])) {
                $model = $model->findLastWithData($this->getData());
            }

            if ($model->exists) {
                $model->setStatusFailed();
                $model->setQueueEndTime();
            }

        } elseif($this->isProccessStatusCompleted()) {
            if (isset($this->getData()['queue_id']) && filled($this->getData()['queue_id'])) {
                $model = $model->findByQueueId($this->getData()['queue_id']);
            } elseif(isset($this->getData()['queue_id']) && blank($this->getData()['queue_id'])) {
                $model = $model->findLastWithData($this->getData());
            }

            if ($model->exists) {
                $model->setStatusCompleted();
                $model->setQueueEndTime();
            }

        }

        $model->save();

        return $model;
    }


}