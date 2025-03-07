<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First get category IDs
        $singleCategoryId = DB::table('package_categories')->where('slug', 'single-package')->first()->id;
        $monthlyCategoryId = DB::table('package_categories')->where('slug', 'monthly-package')->first()->id;
        $thirdPartyCategoryId = DB::table('package_categories')->where('slug', 'third-party')->first()->id;
        $guestPostCategoryId = DB::table('package_categories')->where('slug', 'guest-post')->first()->id;

        $packages = [
            // Single Packages
            [
                'category_id' => $singleCategoryId,
                'third_party_id' => null,
                'guest_post_da' => null,
                'name' => '基础外链套餐',
                'name_en' => 'Basic Backlink Package',
                'slug' => 'basic-backlink-package',
                'description' => '提供5个高质量外链，适合初创网站。',
                'description_zh' => '提供5个高质量外链，适合初创网站。',
                'features' => json_encode([
                    '5个高质量外链',
                    'DA 20+',
                    '永久链接',
                    '7天交付'
                ]),
                'price' => 299.00,
                'original_price' => 399.00,
                'delivery_days' => 7,
                'package_type' => 'single',
                'is_featured' => false,
                'active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $singleCategoryId,
                'third_party_id' => null,
                'guest_post_da' => null,
                'name' => '高级外链套餐',
                'name_en' => 'Premium Backlink Package',
                'slug' => 'premium-backlink-package',
                'description' => '提供10个高质量外链，包含多个DA 30+网站。',
                'description_zh' => '提供10个高质量外链，包含多个DA 30+网站。',
                'features' => json_encode([
                    '10个高质量外链',
                    'DA 30+',
                    '永久链接',
                    '详细报告',
                    '10天交付'
                ]),
                'price' => 599.00,
                'original_price' => 699.00,
                'delivery_days' => 10,
                'package_type' => 'single',
                'is_featured' => true,
                'active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Monthly Packages
            [
                'category_id' => $monthlyCategoryId,
                'third_party_id' => null,
                'guest_post_da' => null,
                'name' => '基础包月套餐',
                'name_en' => 'Basic Monthly Package',
                'slug' => 'basic-monthly-package',
                'description' => '每月提供10个高质量外链，持续提升网站权重。',
                'description_zh' => '每月提供10个高质量外链，持续提升网站权重。',
                'features' => json_encode([
                    '每月10个高质量外链',
                    'DA 20+',
                    '月度SEO报告',
                    '持续服务支持'
                ]),
                'price' => 999.00,
                'original_price' => 1299.00,
                'delivery_days' => 30,
                'package_type' => 'monthly',
                'is_featured' => true,
                'active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $monthlyCategoryId,
                'third_party_id' => null,
                'guest_post_da' => null,
                'name' => '专业包月套餐',
                'name_en' => 'Professional Monthly Package',
                'slug' => 'professional-monthly-package',
                'description' => '每月提供20个高质量外链，包含内容营销和社交媒体推广。',
                'description_zh' => '每月提供20个高质量外链，包含内容营销和社交媒体推广。',
                'features' => json_encode([
                    '每月20个高质量外链',
                    'DA 30+',
                    '2篇原创文章',
                    '社交媒体分享',
                    '详细SEO报告',
                    '专属客户经理'
                ]),
                'price' => 1999.00,
                'original_price' => 2499.00,
                'delivery_days' => 30,
                'package_type' => 'monthly',
                'is_featured' => false,
                'active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Third Party Packages
            [
                'category_id' => $thirdPartyCategoryId,
                'third_party_id' => 'tp-001',
                'guest_post_da' => null,
                'name' => '自助外链服务',
                'name_en' => 'Self-Service Backlinks',
                'slug' => 'self-service-backlinks',
                'description' => '通过我们的平台自助选择外链网站，自定义链接和锚文本。',
                'description_zh' => '通过我们的平台自助选择外链网站，自定义链接和锚文本。',
                'features' => json_encode([
                    '自选外链网站',
                    '自定义锚文本',
                    '即时下单',
                    '按需购买'
                ]),
                'price' => 199.00,
                'original_price' => null,
                'delivery_days' => 5,
                'package_type' => 'third_party',
                'is_featured' => false,
                'active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $thirdPartyCategoryId,
                'third_party_id' => 'tp-002',
                'guest_post_da' => null,
                'name' => '高级自助外链',
                'name_en' => 'Premium Self-Service',
                'slug' => 'premium-self-service',
                'description' => '选择DA 40+的高质量网站，获取强力外链支持。',
                'description_zh' => '选择DA 40+的高质量网站，获取强力外链支持。',
                'features' => json_encode([
                    'DA 40+网站',
                    '自定义内容',
                    '永久链接',
                    '专业SEO建议'
                ]),
                'price' => 399.00,
                'original_price' => 499.00,
                'delivery_days' => 7,
                'package_type' => 'third_party',
                'is_featured' => true,
                'active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Guest Post Packages
            [
                'category_id' => $guestPostCategoryId,
                'third_party_id' => null,
                'guest_post_da' => 30,
                'name' => 'DA 30+ 客座文章',
                'name_en' => 'DA 30+ Guest Post',
                'slug' => 'da-30-guest-post',
                'description' => '在DA 30+的网站上发布原创客座文章，包含1个锚文本链接。',
                'description_zh' => '在DA 30+的网站上发布原创客座文章，包含1个锚文本链接。',
                'features' => json_encode([
                    'DA 30+网站',
                    '500字原创文章',
                    '1个锚文本链接',
                    '永久发布'
                ]),
                'price' => 499.00,
                'original_price' => 599.00,
                'delivery_days' => 14,
                'package_type' => 'guest_post',
                'is_featured' => false,
                'active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $guestPostCategoryId,
                'third_party_id' => null,
                'guest_post_da' => 50,
                'name' => 'DA 50+ 高级客座文章',
                'name_en' => 'DA 50+ Premium Guest Post',
                'slug' => 'da-50-premium-guest-post',
                'description' => '在DA 50+的权威网站上发布高质量原创文章，包含2个锚文本链接。',
                'description_zh' => '在DA 50+的权威网站上发布高质量原创文章，包含2个锚文本链接。',
                'features' => json_encode([
                    'DA 50+权威网站',
                    '1000字原创文章',
                    '2个锚文本链接',
                    '社交媒体分享',
                    '永久发布'
                ]),
                'price' => 999.00,
                'original_price' => 1299.00,
                'delivery_days' => 21,
                'package_type' => 'guest_post',
                'is_featured' => true,
                'active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('packages')->insert($packages);
    }
}