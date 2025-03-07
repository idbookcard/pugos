<?php

protected function schedule(Schedule $schedule)
{
    // Check third-party order status every hour
    $schedule->command('orders:sync-third-party')
             ->hourly()
             ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/orders-sync.log'));
    
    // Process expired crypto payments every 30 minutes
    $schedule->command('payments:process-expired-crypto')
             ->everyThirtyMinutes()
             ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/crypto-payments.log'));
}