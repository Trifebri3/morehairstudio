<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup 
                            {--compress : Kompresi hasil dump ke .sql.gz} 
                            {--retention=7 : Jumlah hari retensi backup sebelum dihapus otomatis} 
                            {--type=full : Tipe backup (full)}';

    protected $description = 'Membuat backup database MORE Hair Studio secara otomatis dan mempruning file usang';

    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Memulai proses backup database MORE Hair Studio...');

        $compress = (bool)$this->option('compress');
        $retention = (int)$this->option('retention');
        $type = (string)$this->option('type');

        $result = $backupService->createBackup($compress, $type);

        if (!$result['success']) {
            $this->error($result['message']);
            return Command::FAILURE;
        }

        $this->info($result['message']);
        $this->table(['Informasi', 'Detail'], [
            ['Nama File', $result['filename']],
            ['Ukuran File', $result['size_formatted']],
            ['Engine Backup', $result['engine']],
            ['Durasi Pengerjaan', $result['duration_ms'] . ' ms'],
            ['Lokasi Simpan', $result['filepath']],
            ['Waktu Pembuatan', $result['created_at']]
        ]);

        // Prune old backups
        if ($retention > 0) {
            $pruned = $backupService->pruneOldBackups($retention);
            if ($pruned > 0) {
                $this->comment("Pembersihan otomatis: {$pruned} file backup yang berusia lebih dari {$retention} hari telah dihapus.");
            }
        }

        return Command::SUCCESS;
    }
}
