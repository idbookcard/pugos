<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Package;
use App\Models\MonthlyOrderDetail;
use App\Models\MonthlyOrderWeeklyTask;

class MonthlyOrderService
{
    /**
     * 为包月订单创建每周任务
     *
     * @param Order $order 订单对象
     * @return array 创建的每周任务
     */
    public function createWeeklyTasks(Order $order)
    {
        // 确保这是包月订单
        if ($order->service_type !== 'monthly') {
            throw new \Exception('只能为包月订单创建每周任务');
        }
        
        $package = $order->package;
        
        // 从套餐中获取每周任务模板
        $taskTemplates = [];
        if ($package && $package->weekly_tasks_template) {
            $taskTemplates = is_string($package->weekly_tasks_template) 
                ? json_decode($package->weekly_tasks_template, true) 
                : $package->weekly_tasks_template;
        }
        
        // 如果没有找到模板，创建默认的每周任务
        if (empty($taskTemplates)) {
            for ($i = 1; $i <= 4; $i++) {
                $taskTemplates[] = [
                    'week_number' => $i,
                    'description' => "第{$i}周任务"
                ];
            }
        }
        
        // 创建每周任务
        $tasks = [];
        foreach ($taskTemplates as $template) {
            $task = new MonthlyOrderWeeklyTask();
            $task->order_id = $order->id;
            $task->week_number = $template['week_number'];
            $task->target_url = $order->monthlyDetail ? $order->monthlyDetail->website : '';
            $task->keywords = $order->monthlyDetail ? $order->monthlyDetail->services_keywords : '';
            $task->description = $template['description'];
            $task->status = 'pending';
            $task->save();
            
            $tasks[] = $task;
        }
        
        return $tasks;
    }
    
    /**
     * 更新周任务的目标URL和关键词
     *
     * @param Order $order 订单对象
     * @param array $weekData 每周数据，格式：['week1_url' => 'url', 'week1_keywords' => 'keywords']
     * @return void
     */
    public function updateWeeklyTasksData(Order $order, array $weekData)
    {
        // 获取订单的所有周任务
        $tasks = MonthlyOrderWeeklyTask::where('order_id', $order->id)->get();
        
        // 更新每周任务的URL和关键词
        foreach ($tasks as $task) {
            $weekNumber = $task->week_number;
            
            $urlKey = "week{$weekNumber}_url";
            $keywordsKey = "week{$weekNumber}_keywords";
            $descriptionKey = "week{$weekNumber}_description";
            
            if (isset($weekData[$urlKey])) {
                $task->target_url = $weekData[$urlKey];
            }
            
            if (isset($weekData[$keywordsKey])) {
                $task->keywords = $weekData[$keywordsKey];
            }
            
            if (isset($weekData[$descriptionKey])) {
                $task->description = $weekData[$descriptionKey];
            }
            
            $task->save();
        }
    }
}