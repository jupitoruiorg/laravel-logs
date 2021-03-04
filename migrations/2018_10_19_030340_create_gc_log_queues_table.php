<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGcLogQueuesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::connection(config('getcode.laravel-logs.log_connection'))->hasTable(config('getcode.laravel-logs.log_queues_table_name'))) {
            return;
        }

        Schema::connection(config('getcode.laravel-logs.log_connection'))
            ->create(config('getcode.laravel-logs.log_queues_table_name'), function (Blueprint $table) {
                $table->increments('id');
                $table->tinyInteger('status')->nullable()->default(0);
                $table->string('queue_id')->nullable();
                $table->dateTime('queue_start_time')->nullable();
                $table->dateTime('queue_end_time')->nullable();
                $table->decimal('queue_execute_time', 5, 2)->nullable();
                $table->string('connection_name')->nullable();
                $table->string('command_name')->nullable();
                $table->integer('caused_by')->nullable();
                $table->timestamps();

                $table->index('queue_id');
                $table->index('caused_by');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::connection(config('getcode.laravel-logs.log_connection'))->dropIfExists(config('getcode.laravel-logs.log_queues_table_name'));
    }
}
