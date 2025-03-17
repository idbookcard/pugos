<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrderService;
use App\Models\ApiOrder;

class SyncApiOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-api-status {--order-id= : 指定同步单个订单ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步API订单状态';

    /**
     * The order service instance.
     *
     * @var \App\Services\OrderService
     */
    protected $orderService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\OrderService  $orderService
     * @return void
     */
    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始同步API订单状态...');

        $orderId = $this->option('order-id');

        try {
            if ($orderId) {
                $apiOrder = ApiOrder::where('order_id', $orderId)->first();
                
                if (!$apiOrder) {
                    $this->error("未找到订单ID {$orderId} 对应的API订单记录");
                    return 1;
                }

                $this->info("正在同步订单ID {$orderId} 的API状态...");
                $result = $this->orderService->syncApiOrderStatus($apiOrder->id);
                
                $this->info("订单同步完成: " . json_encode($result));
            } else {
                $this->info("正在同步所有处理中的API订单...");
                
                // 获取处理中的API订单数量
                $pendingCount = ApiOrder::whereNotNull('api_order_id')
                    ->whereNull('completed_at')
                    ->count();
                    
                $this->info("发现 {$pendingCount} 个待同步的API订单");
                
                // 同步所有订单
                $result = $this->orderService->syncApiOrderStatus();
                
                $this->info("同步完成: 共处理 {$result['updated']} 个订单，其中 {$result['completed']} 个标记为已完成");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('同步API订单状态时发生错误: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
