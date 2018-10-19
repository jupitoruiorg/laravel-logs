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
        if (Schema::hasTable(config('getcode.laravel-logs.log_queues_table_name'))) {
            return;
        }

        Schema::create(config('getcode.laravel-logs.log_queues_table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('queue_id')->nullable();
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
        Schema::dropIfExists(config('getcode.laravel-logs.log_queues_table_name'));
    }
}