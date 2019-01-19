<?php namespace GetCode\LaravelLogs\Mailer\Views;

/**
 * Created by PhpStorm.
 * User: romannebesnuy
 * Date: 2019-01-19
 * Time: 14:42
 */


use App\Order;
use GetCode\LaravelLogs\Models\LogQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueMailCompleted extends Mailable
{
    use Queueable, SerializesModels;

    protected $log;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(LogQueue $log)
    {
        $this->log = $log;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $log = $this->log;

        return $this->view('laravel-logs::emails.queue_completed', compact('log'));
    }
}