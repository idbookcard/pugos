<?php
namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ThirdPartyApiService;
use Illuminate\Console\Command;

class CheckOrderStatuses extends Command
{
    protected $signature = 'orders:check-statuses';
    protected $description = 'Check and update third party order statuses';
    
    public function handle()
    {
        $orders = Order::whereNotNull('third_party_order_id')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'canceled')
            ->get();
        
        $service = new ThirdPartyApiService();
        $updated = 0;
        
        foreach ($orders as $order) {
            $status = $service->checkOrderStatus($order->third_party_order_id);
            
            if ($status) {
                $order->update([
                    'third_party_status' => $status['status'],
                ]);
                
                if ($status['status'] === 'completed') {
                    $order->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    
                    // Create backlink reports from the status data
                    if (isset($status['links'])) {
                        foreach ($status['links'] as $link) {
                            $order->reports()->create([
                                'report_url' => $link['url'],
                                'domain_authority' => $link['authority'] ?? 0,
                                'status' => 'active',
                                'report_data' => json_encode($link),
                                'source' => 'third_party',
                                'placed_at' => now(),
                            ]);
                        }
                    }
                }
                
                $updated++;
            }
        }
        
        $this->info("Updated {$updated} orders");
        return 0;
    }
}