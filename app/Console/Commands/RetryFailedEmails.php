<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetryFailedEmails extends Command
{
    protected $signature = 'mail:retry-failed';
    protected $description = '重试发送失败的邮件';

    public function handle()
    {
        $failedJobs = DB::table('failed_jobs')
            ->where('payload', 'like', '%App\\\Mail\\\%')
            ->get();

        $this->info("找到 {$failedJobs->count()} 个失败的邮件任务");

        foreach ($failedJobs as $job) {
            try {
                $this->info("正在重试 ID: {$job->uuid}");
                
                // 将任务重新放回队列
                \Illuminate\Support\Facades\Artisan::call('queue:retry', [
                    'id' => [$job->uuid]
                ]);
                
                $this->info("任务 {$job->uuid} 已重新放入队列");
            } catch (\Exception $e) {
                $this->error("重试失败: {$e->getMessage()}");
                Log::error("邮件重试失败: {$e->getMessage()}");
            }
        }

        return 0;
    }
}