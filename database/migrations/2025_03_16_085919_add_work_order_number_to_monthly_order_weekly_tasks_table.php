<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('monthly_order_weekly_tasks', function (Blueprint $table) {
            $table->string('work_order_number')->nullable()->after('description')->comment('关联的工单号');
            $table->dateTime('work_order_created_at')->nullable()->after('work_order_number')->comment('工单创建时间');
            $table->string('work_order_status')->nullable()->after('work_order_created_at')->comment('工单状态');
            $table->string('work_order_assignee')->nullable()->after('work_order_status')->comment('工单负责人');
        });
    }

    public function down()
    {
        Schema::table('monthly_order_weekly_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'work_order_number', 
                'work_order_created_at', 
                'work_order_status',
                'work_order_assignee'
            ]);
        });
    }
};
