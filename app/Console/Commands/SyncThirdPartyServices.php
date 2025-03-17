<?php
namespace App\Console\Commands;

use App\Services\ThirdPartyApiService;
use Illuminate\Console\Command;

class SyncThirdPartyServices extends Command
{
    protected $signature = 'services:sync-third-party';
    protected $description = 'Sync services from third party API';
    
    public function handle()
    {
        $service = new ThirdPartyApiService();
        $result = $service->syncProducts();
        
        if ($result) {
            $this->info('Third party services synced successfully');
        } else {
            $this->error('Failed to sync third party services');
        }
        
        return 0;
    }
}
