<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:temp-files {--days=7 : 删除多少天前的临时文件}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理临时文件夹中的旧文件';

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
        $days = (int) $this->option('days');
        $this->info("开始清理 {$days} 天前的临时文件...");
        
        try {
            $disk = Storage::disk('public');
            $tempFolders = ['temp', 'tmp'];
            $cutoffDate = Carbon::now()->subDays($days);
            $totalDeleted = 0;
            
            foreach ($tempFolders as $folder) {
                if (!$disk->exists($folder)) {
                    $this->info("文件夹 {$folder} 不存在，跳过");
                    continue;
                }
                
                $files = $disk->files($folder);
                $deleted = 0;
                
                foreach ($files as $file) {
                    $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));
                    
                    if ($lastModified->lt($cutoffDate)) {
                        $disk->delete($file);
                        $deleted++;
                        $totalDeleted++;
                    }
                }
                
                $this->info("已从 {$folder} 文件夹中删除 {$deleted} 个文件");
            }
            
            $this->info("清理完成，共删除 {$totalDeleted} 个临时文件");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('清理临时文件时发生错误: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
