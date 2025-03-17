<?php

// app/Console/Commands/ProcessExpiredCryptoPayments.php
namespace App\Console\Commands;

use App\Models\CryptoPayment;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessExpiredCryptoPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-expired-crypto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired cryptocurrency payments';

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
        $this->info('Processing expired cryptocurrency payments...');
        
        $now = Carbon::now();
        
        // Find expired pending crypto payments
        $expiredPayments = CryptoPayment::where('status', 'pending')
            ->where('expires_at', '<', $now)
            ->get();
            
        if ($expiredPayments->isEmpty()) {
            $this->info('No expired payments found.');
            return 0;
        }
        
        $this->info("Found {$expiredPayments->count()} expired payments.");
        
        $processed = 0;
        $failed = 0;
        
        foreach ($expiredPayments as $payment) {
            try {
                // Mark crypto payment as expired
                $payment->status = 'expired';
                $payment->save();
                
                // Update associated transaction
                if ($payment->transaction_id) {
                    $transaction = Transaction::find($payment->transaction_id);
                    if ($transaction && $transaction->status == 'pending') {
                        $transaction->status = 'cancelled';
                        $transaction->notes = ($transaction->notes ? $transaction->notes . ' | ' : '') . 'Payment expired';
                        $transaction->save();
                    }
                }
                
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to process expired crypto payment', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("Processed {$processed} expired payments.");
        
        if ($failed > 0) {
            $this->warn("Failed to process {$failed} payments.");
        }
        
        return 0;
    }
}