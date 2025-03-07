<?php

// app/Console/Commands/SyncThirdPartyOrders.php
namespace App\Console\Commands;

use App\Services\ThirdPartyApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncThirdPartyOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-third-party';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize the status of third-party orders';

    /**
     * The third party API service
     */
    protected $apiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ThirdPartyApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting third-party order synchronization...');
        
        try {
            $results = $this->apiService->updateAllOrderStatuses();
            
            $this->info("Processed {$results['total']} orders.");
            $this->info("Updated {$results['updated']} orders.");
            $this->info("Completed {$results['completed']} orders.");
            
            if (isset($results['failed']) && $results['failed'] > 0) {
                $this->warn("Failed to process {$results['failed']} orders.");
            }
            
            Log::info('Third-party order sync completed', $results);
            
            return 0; // Success
        } catch (\Exception $e) {
            $this->error('Failed to sync third-party orders: ' . $e->getMessage());
            Log::error('Third-party order sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1; // Error
        }
    }
}