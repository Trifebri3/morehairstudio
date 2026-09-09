<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class DatabaseRestoreCommand extends Command
{
    protected $signature = 'db:restore 
                            {filename? : Nama file backup yang ingin direstore (misal: backup_more_2026-09-09_220000.sql)} 
                            {--force : Lewati konfirmasi interaktif}';

    protected $description = 'Memulihkan database MORE Hair Studio dari file backup SQL yang tersedia';

    public function handle(DatabaseBackupService $backupService): int
    {
        $filename = $this->argument('filename');
        $force = (bool)$this->option('force');

        $backups = $backupService->listBackups();

        if (empty($backups)) {
            $this->error('Tidak ditemukan file backup di direktori storage/app/backups.');
            return Command::FAILURE;
        }

        if (!$filename) {
            $choices = array_map(fn($b) => "{$b['filename']} ({$b['size_formatted']} - {$b['created_at']})", $backups);
            $selected = $this->choice('Pilih file backup yang ingin Anda restore ke database:', $choices, 0);

            // Extract filename from chosen string
            $parts = explode(' ', $selected);
            $filename = $parts[0];
        }

        if (!$force) {
            $this->warn("PERINGATAN: Memulihkan database akan menimpa seluruh data saat ini dengan data dari file '{$filename}'.");
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan proses restore database ini?')) {
                $this->info('Proses restore dibatalkan oleh pengguna.');
                return Command::SUCCESS;
            }
        }

        $this->info("Memulai proses restore database dari file '{$filename}'...");

        $result = $backupService->restoreBackup($filename);

        if (!$result['success']) {
            $this->error($result['message']);
            return Command::FAILURE;
        }

        $this->info($result['message']);
        return Command::SUCCESS;
    }
}
