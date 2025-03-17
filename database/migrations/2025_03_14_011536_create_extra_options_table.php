<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtraOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. 创建额外选项表
        Schema::create('extra_options', function (Blueprint $table) {
            $table->id();
            $table->string('extra_id')->unique()->comment('第三方API中的选项ID');
            $table->string('code')->nullable()->comment('选项代码');
            $table->string('name')->comment('选项名称');
            $table->string('name_zh')->nullable()->comment('选项中文名称');
            $table->decimal('price', 15, 7)->default(0)->comment('选项价格');
            $table->boolean('is_multiple')->default(false)->comment('是否支持多选');
            $table->boolean('active')->default(true)->comment('是否启用');
            $table->timestamps();
        });
        
        // 2. 向packages表添加新字段
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'available_extras')) {
                $table->json('available_extras')->nullable()->after('features')->comment('可用的额外选项');
            }
            
            if (!Schema::hasColumn('packages', 'min_quantity')) {
                $table->integer('min_quantity')->default(1)->after('delivery_days')->comment('最小订购数量');
            }
            
            if (!Schema::hasColumn('packages', 'is_contextual')) {
                $table->tinyInteger('is_contextual')->default(0)->after('is_api_product')->comment('是否支持上下文');
            }
        });
        
        // 3. 向orders表添加新字段(如果orders表存在)
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'selected_extras')) {
                    if (Schema::hasColumn('orders', 'extra_options')) {
                        $table->json('selected_extras')->nullable()->after('extra_options')->comment('选择的额外选项');
                    } else {
                        $table->json('selected_extras')->nullable()->comment('选择的额外选项');
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extra_options');
        
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'available_extras')) {
                $table->dropColumn('available_extras');
            }
            
            if (Schema::hasColumn('packages', 'min_quantity')) {
                $table->dropColumn('min_quantity');
            }
            
            if (Schema::hasColumn('packages', 'is_contextual')) {
                $table->dropColumn('is_contextual');
            }
        });
        
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'selected_extras')) {
                    $table->dropColumn('selected_extras');
                }
            });
        }
    }
}