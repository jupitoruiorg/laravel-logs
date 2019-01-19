<?php namespace GetCode\LaravelLogs\Mailer;

use GetCode\LaravelLogs\Mailer\Views\QueueMailCompleted;
use GetCode\LaravelLogs\Mailer\Views\QueueMailFailed;
use Illuminate\Support\Facades\Mail;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 2019-01-19
 * Time: 14:23
 */

class QueueMailer
{
    public function sendEmailQueueFailed($log)
    {
        try {
            $message = (new QueueMailFailed($log));

            $emails = config('getcode.laravel-logs.log_queue_notification_emails');

            if (!$emails) {
                return false;
            }

            $emails = explode(',', $emails);

            Mail::to($emails)
                ->queue($message);
        } catch (\Exception $e) {
            //dd($e);
        }
    }

    public function sendEmailQueueCompleted($log)
    {
        try {
            $message = (new QueueMailCompleted($log));

            $emails = config('getcode.laravel-logs.log_queue_notification_emails');

            if (!$emails) {
                return false;
            }

            $emails = explode(',', $emails);

            Mail::to($emails)
                ->queue($message);
        } catch (\Exception $e) {
            //dd($e);
        }

    }
}
