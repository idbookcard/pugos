<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageExtraOptionsTableFixed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 检查表是否已存在，如果存在则删除
        if (Schema::hasTable('package_extra_options')) {
            Schema::drop('package_extra_options');
        }
        
        // 使用原始SQL语句创建表和外键约束
        DB::statement('
            CREATE TABLE `package_extra_options` (
                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `package_id` int(11) NOT NULL,
                `extra_option_id` bigint(20) UNSIGNED NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `package_extra_options_package_id_extra_option_id_unique` (`package_id`,`extra_option_id`),
                KEY `package_extra_options_package_id_index` (`package_id`),
                KEY `package_extra_options_extra_option_id_index` (`extra_option_id`),
                CONSTRAINT `package_extra_options_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
                CONSTRAINT `package_extra_options_extra_option_id_foreign` FOREIGN KEY (`extra_option_id`) REFERENCES `extra_options` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_extra_options');
    }
}