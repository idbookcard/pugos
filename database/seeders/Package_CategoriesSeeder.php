<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Package_CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => '单项套餐',
                'name_en' => 'Single Package',
                'slug' => 'single-package',
                'description' => '一次性购买的单项外链套餐，按需选择。',
                'description_zh' => '一次性购买的单项外链套餐，按需选择。',
                'sort_order' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '包月套餐',
                'name_en' => 'Monthly Package',
                'slug' => 'monthly-package',
                'description' => '按月订阅的外链套餐，定期提供外链服务。',
                'description_zh' => '按月订阅的外链套餐，定期提供外链服务。',
                'sort_order' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '自助下单',
                'name_en' => 'Self-Service (Third Party)',
                'slug' => 'third-party',
                'description' => '通过第三方平台自助下单的外链服务。',
                'description_zh' => '通过第三方平台自助下单的外链服务。',
                'sort_order' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Guest Post',
                'name_en' => 'Guest Post',
                'slug' => 'guest-post',
                'description' => '在高质量站点发布客座文章的外链服务。',
                'description_zh' => '在高质量站点发布客座文章的外链服务。',
                'sort_order' => 4,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('package_categories')->insert($categories);
    }
}