<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGetCodeFieldsToGcLogQueuesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (
            Schema::hasTable(config('getcode.laravel-logs.log_queues_table_name'))
                &&
            (
                Schema::hasColumn(config('getcode.laravel-logs.log_queues_table_name'), 'file_name')
                    &&
                Schema::hasColumn(config('getcode.laravel-logs.log_queues_table_name'), 'pdf_view')
                    &&
                Schema::hasColumn(config('getcode.laravel-logs.log_queues_table_name'), 'export_source')
            )
        ) {
            return;
        }

        Schema::table(config('getcode.laravel-logs.log_queues_table_name'), function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('command_name');
            $table->string('pdf_view')->nullable()->after('file_name');
            $table->string('export_source')->nullable()->after('pdf_view');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    	if (
    		Schema::hasTable(config('getcode.laravel-logs.log_queues_table_name'))
    			&&
			(
				Schema::hasColumn(config('getcode.laravel-logs.log_queues_table_name'), 'file_name')
					&&
				Schema::hasColumn(config('getcode.laravel-logs.log_queues_table_name'), 'pdf_view')
					&&
				Schema::hasColumn(config('getcode.laravel-logs.log_queues_table_name'), 'export_source')
			)
		) {
            Schema::table(config('getcode.laravel-logs.log_queues_table_name'), function (Blueprint $table) {
				$table->dropColumn(['file_name', 'pdf_view', 'export_source']);
		    });
        }

    }
}