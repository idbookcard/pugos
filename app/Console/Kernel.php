<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SyncApiOrderStatus;
use App\Console\Commands\ExpirePendingCryptoPayments;
use App\Console\Commands\CleanupTemporaryFiles;
use App\Console\Commands\SyncApiProducts;
use App\Console\Commands\DatabaseBackup;

class Kernel extends ConsoleKernel
{
    /**
     * 应用的命令列表
     */
    protected $commands = [
        SyncApiOrderStatus::class,
        ExpirePendingCryptoPayments::class,
        CleanupTemporaryFiles::class,
        SyncApiProducts::class,
        DatabaseBackup::class,
    ];

    /**
     * 定义应用的命令计划
     */
    protected function schedule(Schedule $schedule)
    {
        // 同步API订单状态（每30分钟）
        $schedule->command('orders:sync-api-status')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/api-sync.log'));
        
        // 检查并过期超时的加密货币支付（每小时）
        $schedule->command('payments:expire-crypto')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/crypto-payments.log'));
        
        // 清理临时文件（每天凌晨）
        $schedule->command('cleanup:temp-files')
                 ->dailyAt('01:00')
                 ->appendOutputTo(storage_path('logs/cleanup.log'));
        
        // 同步API产品（每天）
        $schedule->command('products:sync-api')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/api-products-sync.log'));
        
        // 数据库备份（每天）
        $schedule->command('db:backup')
                 ->dailyAt('03:00')
                 ->appendOutputTo(storage_path('logs/db-backup.log'));
    }

    /**
     * 注册命令
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}