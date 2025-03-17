<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CryptoPayment;
use Carbon\Carbon;

class ExpirePendingCryptoPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:expire-crypto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '过期已超时的加密货币支付';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始检查过期的加密货币支付...');
        
        try {
            // 查找已过期但状态仍为pending的支付
            $expiredPayments = CryptoPayment::where('status', 'pending')
                ->where('expires_at', '<', Carbon::now())
                ->get();
                
            $count = $expiredPayments->count();
            $this->info("发现 {$count} 个已过期的加密货币支付记录");
            
            if ($count === 0) {
                return 0;
            }
            
            foreach ($expiredPayments as $payment) {
                // 更新状态为过期
                $payment->update([
                    'status' => 'expired'
                ]);
                
                // 如果关联了交易记录，也更新交易状态
                if ($payment->transaction_id) {
                    $transaction = \App\Models\Transaction::find($payment->transaction_id);
                    
                    if ($transaction && $transaction->status === 'pending') {
                        $transaction->update([
                            'status' => 'failed',
                            'notes' => '支付超时已过期'
                        ]);
                    }
                }
                
                $this->info("已将支付ID {$payment->id} 标记为过期");
            }
            
            $this->info("处理完成，共过期 {$count} 个加密货币支付记录");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('处理过期加密货币支付时发生错误: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}