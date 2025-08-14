<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutomatedBackupSystem extends Command
{
    protected $signature = 'hospital:backup {--type=full : Type of backup (full, data, structure)} {--compress : Compress the backup file}';
    protected $description = 'Create automated backups of hospital database and files';

    public function handle()
    {
        $this->info('💾 HOSPITAL AUTOMATED BACKUP SYSTEM');
        $this->info('====================================');

        $backupType = $this->option('type');
        $compress = $this->option('compress');

        try {
            switch ($backupType) {
                case 'full':
                    $this->createFullBackup($compress);
                    break;
                case 'data':
                    $this->createDataBackup($compress);
                    break;
                case 'structure':
                    $this->createStructureBackup($compress);
                    break;
                default:
                    $this->createFullBackup($compress);
            }

            // Clean old backups
            $this->cleanOldBackups();

            $this->info('✅ Backup process completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function createFullBackup($compress = false)
    {
        $this->info('🔄 Creating full database backup...');

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "hospital_full_backup_{$timestamp}.sql";
        
        $this->createDatabaseBackup($filename, true, true);
        
        if ($compress) {
            $this->compressBackup($filename);
        }

        $this->info("✅ Full backup created: {$filename}");
    }

    private function createDataBackup($compress = false)
    {
        $this->info('🔄 Creating data-only backup...');

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "hospital_data_backup_{$timestamp}.sql";
        
        $this->createDatabaseBackup($filename, true, false);
        
        if ($compress) {
            $this->compressBackup($filename);
        }

        $this->info("✅ Data backup created: {$filename}");
    }

    private function createStructureBackup($compress = false)
    {
        $this->info('🔄 Creating structure-only backup...');

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "hospital_structure_backup_{$timestamp}.sql";
        
        $this->createDatabaseBackup($filename, false, true);
        
        if ($compress) {
            $this->compressBackup($filename);
        }

        $this->info("✅ Structure backup created: {$filename}");
    }

    private function createDatabaseBackup($filename, $includeData = true, $includeStructure = true)
    {
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');

        // Create backups directory if it doesn't exist
        $backupPath = storage_path('app/backups');
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filePath = $backupPath . '/' . $filename;

        // Build mysqldump command
        $command = "mysqldump";
        $command .= " --host={$dbHost}";
        $command .= " --port={$dbPort}";
        $command .= " --user={$dbUser}";
        
        if ($dbPassword) {
            $command .= " --password={$dbPassword}";
        }

        // Add options based on backup type
        if (!$includeData) {
            $command .= " --no-data";
        }
        
        if (!$includeStructure) {
            $command .= " --no-create-info";
        }

        $command .= " --single-transaction";
        $command .= " --routines";
        $command .= " --triggers";
        $command .= " {$dbName}";
        $command .= " > {$filePath}";

        // Execute the backup command
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Database backup failed with return code: {$returnCode}");
        }

        // Add backup metadata
        $this->addBackupMetadata($filePath);
    }

    private function addBackupMetadata($filePath)
    {
        $metadata = [
            '-- Hospital Management System Backup',
            '-- Created: ' . Carbon::now()->toDateTimeString(),
            '-- Server: ' . config('app.url'),
            '-- Laravel Version: ' . app()->version(),
            '-- Database: ' . config('database.connections.mysql.database'),
            '-- Backup Size: ' . $this->formatBytes(filesize($filePath)),
            '--',
            ''
        ];

        $backupContent = file_get_contents($filePath);
        $backupWithMetadata = implode("\n", $metadata) . $backupContent;
        file_put_contents($filePath, $backupWithMetadata);
    }

    private function compressBackup($filename)
    {
        $this->info('🗜️ Compressing backup file...');

        $backupPath = storage_path('app/backups');
        $originalFile = $backupPath . '/' . $filename;
        $compressedFile = $originalFile . '.gz';

        if (function_exists('gzencode')) {
            $data = file_get_contents($originalFile);
            $compressed = gzencode($data, 9);
            file_put_contents($compressedFile, $compressed);
            
            // Remove original file
            unlink($originalFile);
            
            $originalSize = strlen($data);
            $compressedSize = filesize($compressedFile);
            $compressionRatio = round((1 - $compressedSize / $originalSize) * 100, 2);
            
            $this->info("✅ Backup compressed: {$compressionRatio}% size reduction");
        } else {
            $this->warn('⚠️ gzip compression not available, skipping compression');
        }
    }

    private function cleanOldBackups()
    {
        $this->info('🧹 Cleaning old backup files...');

        $backupPath = storage_path('app/backups');
        $retentionDays = 30; // Keep backups for 30 days

        if (!is_dir($backupPath)) {
            return;
        }

        $files = glob($backupPath . '/hospital_*_backup_*.sql*');
        $deletedCount = 0;

        foreach ($files as $file) {
            $fileTime = filemtime($file);
            $daysDiff = (time() - $fileTime) / (60 * 60 * 24);

            if ($daysDiff > $retentionDays) {
                unlink($file);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("✅ Cleaned {$deletedCount} old backup files");
        } else {
            $this->info('✅ No old backup files to clean');
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Schedule this command to run automatically
     */
    public function schedule()
    {
        // This method would be called from app/Console/Kernel.php
        // to schedule automatic backups
        
        // Example scheduling:
        // Daily full backup at 2 AM
        // $schedule->command('hospital:backup --type=full --compress')->dailyAt('02:00');
        
        // Weekly structure backup
        // $schedule->command('hospital:backup --type=structure --compress')->weekly();
    }
}
