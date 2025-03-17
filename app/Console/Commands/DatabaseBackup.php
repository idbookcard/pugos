<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep=14 : 保留最近几天的备份} {--compress : 压缩备份文件}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '备份数据库';

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
        $this->info('开始数据库备份...');
        
        $keep = (int) $this->option('keep');
        $compress = $this->option('compress');
        
        try {
            // 创建备份目录
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            // 生成备份文件名
            $filename = 'backup_' . Carbon::now()->format('Y-m-d_His') . '.sql';
            $backupFile = $backupPath . '/' . $filename;
            
            // 获取数据库配置
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            // 执行mysqldump命令
            $command = "mysqldump -h {$host} -u {$username} -p{$password} {$database} > {$backupFile}";
            
            // 使用Process执行命令
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(3600); // 最大执行时间1小时
            $process->run();
            
            // 检查执行结果
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            $this->info("数据库备份成功: {$backupFile}");
            
            // 如果需要压缩
            if ($compress) {
                $compressedFile = $backupFile . '.gz';
                $this->info('正在压缩备份文件...');
                
                $compressProcess = Process::fromShellCommandline("gzip {$backupFile}");
                $compressProcess->run();
                
                if (!$compressProcess->isSuccessful()) {
                    $this->warn('压缩文件失败，将保留原始SQL文件');
                } else {
                    $this->info("备份文件已压缩: {$compressedFile}");
                    $backupFile = $compressedFile;
                }
            }
            
            // 清理旧备份
            $this->cleanupOldBackups($backupPath, $keep);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('数据库备份失败: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * 清理旧备份文件
     *
     * @param string $backupPath
     * @param int $keep
     * @return void
     */
    protected function cleanupOldBackups($backupPath, $keep)
    {
        $this->info("开始清理 {$keep} 天前的备份文件...");
        
        $cutoffDate = Carbon::now()->subDays($keep);
        $deleted = 0;
        
        if ($handle = opendir($backupPath)) {
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                
                $filePath = $backupPath . '/' . $file;
                $lastModified = Carbon::createFromTimestamp(filemtime($filePath));
                
                if ($lastModified->lt($cutoffDate)) {
                    unlink($filePath);
                    $deleted++;
                }
            }
            closedir($handle);
        }
        
        $this->info("清理完成，已删除 {$deleted} 个旧备份文件");
    }
}