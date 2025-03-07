<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Package;
use App\Models\PackageCategory;
use Carbon\Carbon;

class PackageService
{
    protected $lastError;

    /**
     * 获取最后的错误信息
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * 获取所有活跃的套餐分类
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories()
    {
        return PackageCategory::where('active', 1)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * 获取分类下的所有套餐
     *
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPackagesByCategory($categoryId)
    {
        return Package::where('category_id', $categoryId)
            ->where('active', 1)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * 获取单个套餐详情
     *
     * @param int $packageId
     * @return Package|null
     */
    public function getPackage($packageId)
    {
        return Package::find($packageId);
    }

    /**
     * 创建套餐分类
     *
     * @param array $data
     * @return PackageCategory|bool
     */
    public function createCategory($data)
    {
        try {
            DB::beginTransaction();
            
            $category = new PackageCategory();
            $category->name = $data['name'];
            $category->name_zh = $data['name_zh'] ?? $data['name'];
            $category->description = $data['description'] ?? '';
            $category->description_zh = $data['description_zh'] ?? '';
            $category->sort_order = $data['sort_order'] ?? 0;
            $category->active = $data['active'] ?? 1;
            $category->save();
            
            DB::commit();
            
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "创建分类失败: " . $e->getMessage();
            Log::error('创建套餐分类失败', [
                'exception' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * 更新套餐分类
     *
     * @param int $categoryId
     * @param array $data
     * @return PackageCategory|bool
     */
    public function updateCategory($categoryId, $data)
    {
        $category = PackageCategory::find($categoryId);
        
        if (!$category) {
            $this->lastError = "找不到指定分类";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            if (isset($data['name'])) {
                $category->name = $data['name'];
            }
            
            if (isset($data['name_zh'])) {
                $category->name_zh = $data['name_zh'];
            }
            
            if (isset($data['description'])) {
                $category->description = $data['description'];
            }
            
            if (isset($data['description_zh'])) {
                $category->description_zh = $data['description_zh'];
            }
            
            if (isset($data['sort_order'])) {
                $category->sort_order = $data['sort_order'];
            }
            
            if (isset($data['active'])) {
                $category->active = $data['active'];
            }
            
            $category->save();
            
            DB::commit();
            
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "更新分类失败: " . $e->getMessage();
            Log::error('更新套餐分类失败', [
                'exception' => $e->getMessage(),
                'category_id' => $categoryId,
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * 删除套餐分类
     *
     * @param int $categoryId
     * @return bool
     */
    public function deleteCategory($categoryId)
    {
        $category = PackageCategory::find($categoryId);
        
        if (!$category) {
            $this->lastError = "找不到指定分类";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            // 检查分类下是否有套餐
            $packageCount = Package::where('category_id', $categoryId)->count();
            
            if ($packageCount > 0) {
                $this->lastError = "该分类下有 {$packageCount} 个套餐，无法删除";
                return false;
            }
            
            $category->delete();
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "删除分类失败: " . $e->getMessage();
            Log::error('删除套餐分类失败', [
                'exception' => $e->getMessage(),
                'category_id' => $categoryId
            ]);
            return false;
        }
    }

    /**
     * 创建套餐
     *
     * @param array $data
     * @return Package|bool
     */
    public function createPackage($data)
    {
        try {
            DB::beginTransaction();
            
            $package = new Package();
            $package->category_id = $data['category_id'];
            $package->name = $data['name'];
            $package->name_zh = $data['name_zh'] ?? $data['name'];
            $package->description = $data['description'] ?? '';
            $package->description_zh = $data['description_zh'] ?? '';
            $package->price = $data['price'];
            $package->original_price = $data['original_price'] ?? $data['price'];
            $package->delivery_days = $data['delivery_days'] ?? 7;
            $package->sort_order = $data['sort_order'] ?? 0;
            $package->is_featured = $data['is_featured'] ?? 0;
            $package->min_quantity = $data['min_quantity'] ?? 1;
            $package->max_quantity = $data['max_quantity'] ?? 1;
            $package->package_type = $data['package_type'] ?? 'single';
            $package->active = $data['active'] ?? 1;
            
            if (!empty($data['features'])) {
                $package->features = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
            }
            
            $package->save();
            
            DB::commit();
            
            return $package;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "创建套餐失败: " . $e->getMessage();
            Log::error('创建套餐失败', [
                'exception' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * 更新套餐
     *
     * @param int $packageId
     * @param array $data
     * @return Package|bool
     */
    public function updatePackage($packageId, $data)
    {
        $package = Package::find($packageId);
        
        if (!$package) {
            $this->lastError = "找不到指定套餐";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            if (isset($data['category_id'])) {
                $package->category_id = $data['category_id'];
            }
            
            if (isset($data['name'])) {
                $package->name = $data['name'];
            }
            
            if (isset($data['name_zh'])) {
                $package->name_zh = $data['name_zh'];
            }
            
            if (isset($data['description'])) {
                $package->description = $data['description'];
            }
            
            if (isset($data['description_zh'])) {
                $package->description_zh = $data['description_zh'];
            }
            
            if (isset($data['price'])) {
                $package->price = $data['price'];
            }
            
            if (isset($data['original_price'])) {
                $package->original_price = $data['original_price'];
            }
            
            if (isset($data['delivery_days'])) {
                $package->delivery_days = $data['delivery_days'];
            }
            
            if (isset($data['sort_order'])) {
                $package->sort_order = $data['sort_order'];
            }
            
            if (isset($data['is_featured'])) {
                $package->is_featured = $data['is_featured'];
            }
            
            if (isset($data['min_quantity'])) {
                $package->min_quantity = $data['min_quantity'];
            }
            
            if (isset($data['max_quantity'])) {
                $package->max_quantity = $data['max_quantity'];
            }
            
            if (isset($data['package_type'])) {
                $package->package_type = $data['package_type'];
            }
            
            if (isset($data['active'])) {
                $package->active = $data['active'];
            }
            
            if (isset($data['features'])) {
                $package->features = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
            }
            
            $package->save();
            
            DB::commit();
            
            return $package;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "更新套餐失败: " . $e->getMessage();
            Log::error('更新套餐失败', [
                'exception' => $e->getMessage(),
                'package_id' => $packageId,
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * 删除套餐
     *
     * @param int $packageId
     * @return bool
     */
    public function deletePackage($packageId)
    {
        $package = Package::find($packageId);
        
        if (!$package) {
            $this->lastError = "找不到指定套餐";
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            // 检查是否有关联的订单
            $orderCount = \App\Models\Order::where('package_id', $packageId)->count();
            
            if ($orderCount > 0) {
                $this->lastError = "该套餐下有 {$orderCount} 个订单，无法删除";
                return false;
            }
            
            $package->delete();
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->lastError = "删除套餐失败: " . $e->getMessage();
            Log::error('删除套餐失败', [
                'exception' => $e->getMessage(),
                'package_id' => $packageId
            ]);
            return false;
        }
    }
    
    /**
     * 从PDF内容中导入套餐
     *
     * @param array $pdfData 
     * @param int $categoryId
     * @return array
     */
    public function importPackagesFromPdf($pdfData, $categoryId)
    {
        $results = [
            'total' => count($pdfData),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($pdfData as $item) {
            $packageData = [
                'category_id' => $categoryId,
                'name' => $item['name'] ?? '',
                'name_zh' => $item['name_zh'] ?? ($item['name'] ?? ''),
                'description' => $item['description'] ?? '',
                'description_zh' => $item['description_zh'] ?? ($item['description'] ?? ''),
                'price' => $item['price'] ?? 0,
                'delivery_days' => $item['delivery_days'] ?? 7,
                'package_type' => 'single',
                'active' => 1
            ];
            
            $package = $this->createPackage($packageData);
            
            if ($package) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'name' => $packageData['name'],
                    'error' => $this->getLastError()
                ];
            }
        }
        
        return $results;
    }

    /**
     * 导入包月套餐数据
     *
     * @param array $monthlyData
     * @param int $categoryId
     * @return array
     */
    public function importMonthlyPackages($monthlyData, $categoryId)
    {
        $results = [
            'total' => count($monthlyData),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($monthlyData as $item) {
            $packageData = [
                'category_id' => $categoryId,
                'name' => $item['name'] ?? '',
                'name_zh' => $item['name_zh'] ?? ($item['name'] ?? ''),
                'description' => $this->formatMonthlyDescription($item),
                'description_zh' => $this->formatMonthlyDescription($item, true),
                'price' => $item['price'] ?? 0,
                'delivery_days' => 30,
                'package_type' => 'monthly',
                'active' => 1,
                'features' => $this->formatMonthlyFeatures($item)
            ];
            
            $package = $this->createPackage($packageData);
            
            if ($package) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'name' => $packageData['name'],
                    'error' => $this->getLastError()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 格式化包月套餐描述
     *
     * @param array $item
     * @param bool $isChinese
     * @return string
     */
    protected function formatMonthlyDescription($item, $isChinese = false)
    {
        $description = '';
        
        if ($isChinese) {
            $description = "<h4>{$item['name_zh']}</h4><p>每周活动概述：</p><ul>";
            
            foreach ($item['weeks'] as $weekIndex => $week) {
                $weekNum = $weekIndex + 1;
                $description .= "<li><strong>第 {$weekNum} 周:</strong> {$week['description_zh']}</li>";
            }
            
            $description .= "</ul><p>这个包月套餐持续30天，每周都会为您的网站执行不同的SEO优化活动，帮助您的网站获得稳定的排名提升。</p>";
        } else {
            $description = "<h4>{$item['name']}</h4><p>Weekly activities overview:</p><ul>";
            
            foreach ($item['weeks'] as $weekIndex => $week) {
                $weekNum = $weekIndex + 1;
                $description .= "<li><strong>Week {$weekNum}:</strong> {$week['description']}</li>";
            }
            
            $description .= "</ul><p>This monthly package lasts for 30 days, with different SEO activities executed each week to help your website gain consistent ranking improvements.</p>";
        }
        
        return $description;
    }
    
    /**
     * 格式化包月套餐功能特点
     *
     * @param array $item
     * @return string
     */
    protected function formatMonthlyFeatures($item)
    {
        $features = [];
        
        foreach ($item['weeks'] as $weekIndex => $week) {
            $weekNum = $weekIndex + 1;
            $weekFeatures = [];
            
            foreach ($week['activities'] as $activity) {
                $weekFeatures[] = $activity;
            }
            
            $features["week{$weekNum}"] = $weekFeatures;
        }
        
        return json_encode($features);
    }