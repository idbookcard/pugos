<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SEOeStoreApiService;

class SyncApiProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-api {--force : 强制更新所有产品}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步第三方API产品数据';

    /**
     * The SEOeStore API service instance.
     *
     * @var \App\Services\SEOeStoreApiService
     */
    protected $apiService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\SEOeStoreApiService  $apiService
     * @return void
     */
    public function __construct(SEOeStoreApiService $apiService)
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
        $this->info('开始同步API产品数据...');
        
        try {
            $force = $this->option('force');
            
            if ($force) {
                $this->info('强制更新模式已开启，将更新所有产品');
            }
            
            $result = $this->apiService->syncProducts($force);
            
            $this->info("同步完成：");
            $this->info("- 新增产品：{$result['created']}");
            $this->info("- 更新产品：{$result['updated']}");
            $this->info("- 总API产品数：{$result['total']}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('同步API产品时发生错误: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}