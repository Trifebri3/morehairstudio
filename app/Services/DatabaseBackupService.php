<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseBackupService
{
    protected string $backupDir;
    protected string $dbHost;
    protected string $dbPort;
    protected string $dbName;
    protected string $dbUser;
    protected string $dbPass;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }

        $this->dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $this->dbPort = (string)config('database.connections.mysql.port', '3306');
        $this->dbName = config('database.connections.mysql.database', 'morenewagustus');
        $this->dbUser = config('database.connections.mysql.username', 'root');
        $this->dbPass = config('database.connections.mysql.password', '');
    }

    /**
     * Create a full database backup with Dual-Engine (mysqldump CLI + PDO Stream fallback).
     */
    public function createBackup(bool $compress = false, string $type = 'full'): array
    {
        $startTime = microtime(true);
        $timestamp = date('Y-m-d_His');
        $filename = "backup_more_{$timestamp}.sql";
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        $engineUsed = 'mysqldump_binary';
        $success = false;
        $errorMessage = '';

        // 1. Try mysqldump binary
        $mysqldumpPath = $this->findMysqldumpBinary();
        if ($mysqldumpPath) {
            try {
                $passwordParam = !empty($this->dbPass) ? "--password=" . escapeshellarg($this->dbPass) : "";
                $cmd = sprintf(
                    '"%s" --user=%s %s --host=%s --port=%s --single-transaction --routines --triggers %s > "%s"',
                    $mysqldumpPath,
                    escapeshellarg($this->dbUser),
                    $passwordParam,
                    escapeshellarg($this->dbHost),
                    escapeshellarg($this->dbPort),
                    escapeshellarg($this->dbName),
                    $filePath
                );

                exec($cmd, $output, $returnVar);

                if ($returnVar === 0 && File::exists($filePath) && File::size($filePath) > 0) {
                    $success = true;
                } else {
                    $errorMessage = "mysqldump binary exit code: {$returnVar}";
                }
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        // 2. Fallback to PHP PDO Streaming Engine if binary dump failed or was not found
        if (!$success) {
            $engineUsed = 'php_pdo_stream';
            try {
                $this->dumpDatabaseViaPdo($filePath);
                $success = true;
            } catch (\Exception $e) {
                $errorMessage = "PDO Dump Failed: " . $e->getMessage();
                $success = false;
            }
        }

        if (!$success) {
            Log::error("Database backup failed: " . $errorMessage);
            $this->logAudit('database_backup_failed', ['error' => $errorMessage]);
            return [
                'success' => false,
                'message' => 'Gagal membuat backup database: ' . $errorMessage,
                'file' => null,
                'size_bytes' => 0,
                'duration_ms' => 0
            ];
        }

        // 3. Compress if requested
        if ($compress && File::exists($filePath)) {
            $gzPath = $filePath . '.gz';
            $gz = gzopen($gzPath, 'w9');
            $fp = fopen($filePath, 'r');
            while (!feof($fp)) {
                gzwrite($gz, fread($fp, 1024 * 512));
            }
            fclose($fp);
            gzclose($gz);
            File::delete($filePath);
            $filePath = $gzPath;
            $filename = basename($gzPath);
        }

        $fileSize = File::exists($filePath) ? File::size($filePath) : 0;
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $this->logAudit('database_backup_created', [
            'filename' => $filename,
            'size_bytes' => $fileSize,
            'engine' => $engineUsed,
            'type' => $type,
            'duration_ms' => $durationMs
        ]);

        return [
            'success' => true,
            'message' => "Backup database berhasil dibuat menggunakan {$engineUsed} ({$this->formatBytes($fileSize)} dalam {$durationMs} ms).",
            'filename' => $filename,
            'filepath' => $filePath,
            'size_bytes' => $fileSize,
            'size_formatted' => $this->formatBytes($fileSize),
            'engine' => $engineUsed,
            'duration_ms' => $durationMs,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Restore database from an existing SQL backup file.
     */
    public function restoreBackup(string $filename): array
    {
        $cleanFilename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $cleanFilename;

        if (!File::exists($filePath)) {
            return [
                'success' => false,
                'message' => "File backup '{$cleanFilename}' tidak ditemukan di server."
            ];
        }

        $startTime = microtime(true);
        $engineUsed = 'mysql_binary';
        $success = false;
        $errorMessage = '';

        // If compressed, decompress temporarily
        $isGz = str_ends_with($cleanFilename, '.gz');
        $restoreSqlPath = $filePath;

        if ($isGz) {
            $restoreSqlPath = $this->backupDir . DIRECTORY_SEPARATOR . 'temp_restore_' . time() . '.sql';
            $gz = gzopen($filePath, 'r');
            $out = fopen($restoreSqlPath, 'w');
            while (!gzeof($gz)) {
                fwrite($out, gzread($gz, 1024 * 512));
            }
            fclose($out);
            gzclose($gz);
        }

        // 1. Try mysql CLI binary
        $mysqlBin = $this->findMysqlBinary();
        if ($mysqlBin) {
            try {
                $passwordParam = !empty($this->dbPass) ? "--password=" . escapeshellarg($this->dbPass) : "";
                $cmd = sprintf(
                    '"%s" --user=%s %s --host=%s --port=%s %s < "%s"',
                    $mysqlBin,
                    escapeshellarg($this->dbUser),
                    $passwordParam,
                    escapeshellarg($this->dbHost),
                    escapeshellarg($this->dbPort),
                    escapeshellarg($this->dbName),
                    $restoreSqlPath
                );

                exec($cmd, $output, $returnVar);

                if ($returnVar === 0) {
                    $success = true;
                } else {
                    $errorMessage = "mysql binary exit code: {$returnVar}";
                }
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        // 2. Fallback to PHP PDO multi-query restore
        if (!$success) {
            $engineUsed = 'php_pdo_stream';
            try {
                $this->restoreDatabaseViaPdo($restoreSqlPath);
                $success = true;
            } catch (\Exception $e) {
                $errorMessage = "PDO Restore Failed: " . $e->getMessage();
                $success = false;
            }
        }

        // Cleanup temp file if decompressed
        if ($isGz && File::exists($restoreSqlPath)) {
            File::delete($restoreSqlPath);
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        if ($success) {
            $this->logAudit('database_restore_executed', [
                'filename' => $cleanFilename,
                'engine' => $engineUsed,
                'duration_ms' => $durationMs
            ]);

            return [
                'success' => true,
                'message' => "Database MORE Hair Studio berhasil direstore dari file '{$cleanFilename}' menggunakan engine {$engineUsed} ({$durationMs} ms).",
                'duration_ms' => $durationMs
            ];
        }

        Log::error("Database restore failed: " . $errorMessage);
        $this->logAudit('database_restore_failed', ['filename' => $cleanFilename, 'error' => $errorMessage]);

        return [
            'success' => false,
            'message' => "Gagal merestore database: " . $errorMessage
        ];
    }

    /**
     * List all database backup files with size and metadata.
     */
    public function listBackups(): array
    {
        if (!File::exists($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $name = $file->getFilename();
            if (!str_ends_with($name, '.sql') && !str_ends_with($name, '.sql.gz')) {
                continue;
            }

            $size = $file->getSize();
            $mtime = $file->getMTime();
            $carbonDate = Carbon::createFromTimestamp($mtime);

            $backups[] = [
                'filename' => $name,
                'size_bytes' => $size,
                'size_formatted' => $this->formatBytes($size),
                'created_at' => $carbonDate->format('Y-m-d H:i:s'),
                'relative_age' => $carbonDate->diffForHumans(),
                'is_compressed' => str_ends_with($name, '.gz'),
                'timestamp' => $mtime
            ];
        }

        // Sort descending (newest first)
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $cleanFilename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $cleanFilename;

        if (File::exists($filePath)) {
            $deleted = File::delete($filePath);
            if ($deleted) {
                $this->logAudit('database_backup_deleted', ['filename' => $cleanFilename]);
            }
            return $deleted;
        }

        return false;
    }

    /**
     * Delete backups older than specified days (auto-prune retention).
     */
    public function pruneOldBackups(int $retentionDays = 7): int
    {
        $backups = $this->listBackups();
        $cutoff = Carbon::now()->subDays($retentionDays)->timestamp;
        $prunedCount = 0;

        foreach ($backups as $b) {
            if ($b['timestamp'] < $cutoff) {
                if ($this->deleteBackup($b['filename'])) {
                    $prunedCount++;
                }
            }
        }

        return $prunedCount;
    }

    /**
     * Native PHP PDO Database Dumper (100% portable, works without mysqldump binary).
     */
    protected function dumpDatabaseViaPdo(string $filePath): void
    {
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            throw new \Exception("Cannot open file for writing: {$filePath}");
        }

        $now = date('Y-m-d H:i:s');
        fwrite($handle, "-- --------------------------------------------------------\n");
        fwrite($handle, "-- MORE Hair Studio - Database Backup Dump\n");
        fwrite($handle, "-- Generated: {$now}\n");
        fwrite($handle, "-- Host: {$this->dbHost}:{$this->dbPort} Database: {$this->dbName}\n");
        fwrite($handle, "-- Engine: PHP PDO Stream Dumper\n");
        fwrite($handle, "-- --------------------------------------------------------\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET AUTOCOMMIT = 0;\n");
        fwrite($handle, "START TRANSACTION;\n");
        fwrite($handle, "SET time_zone = '+07:00';\n\n");

        $tables = DB::select('SHOW TABLES');
        $keyName = 'Tables_in_' . $this->dbName;

        foreach ($tables as $tRow) {
            $tableName = $tRow->$keyName ?? array_values((array)$tRow)[0];

            fwrite($handle, "--\n-- Table structure for table `{$tableName}`\n--\n\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

            $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createTableSql = $createTableResult[0]->{'Create Table'} ?? null;
            if ($createTableSql) {
                fwrite($handle, $createTableSql . ";\n\n");
            }

            // Dump data in chunks of 250 rows
            $rowCount = DB::table($tableName)->count();
            if ($rowCount > 0) {
                fwrite($handle, "--\n-- Dumping data for table `{$tableName}`\n--\n\n");

                $offset = 0;
                $chunkSize = 250;
                while ($offset < $rowCount) {
                    $rows = DB::table($tableName)->offset($offset)->limit($chunkSize)->get();
                    if ($rows->isEmpty()) {
                        break;
                    }

                    $insertLines = [];
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ((array)$row as $val) {
                            if ($val === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = DB::getPdo()->quote((string)$val);
                            }
                        }
                        $insertLines[] = "(" . implode(', ', $values) . ")";
                    }

                    if (!empty($insertLines)) {
                        $firstRowKeys = array_keys((array)$rows->first());
                        $escapedColumns = array_map(fn($col) => "`{$col}`", $firstRowKeys);
                        $colList = implode(', ', $escapedColumns);

                        fwrite($handle, "INSERT INTO `{$tableName}` ({$colList}) VALUES\n");
                        fwrite($handle, implode(",\n", $insertLines) . ";\n");
                    }

                    $offset += $chunkSize;
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "COMMIT;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($handle, "-- Dump completed on {$now}\n");

        fclose($handle);
    }

    /**
     * Native PHP PDO Database Restorer (Executes SQL file statement by statement).
     */
    protected function restoreDatabaseViaPdo(string $filePath): void
    {
        $pdo = DB::getPdo();
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Cannot open file for reading: {$filePath}");
        }

        $query = '';
        while (!feof($handle)) {
            $line = fgets($handle);
            $trimmed = trim($line);

            // Skip empty lines and comments
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $query .= $line;

            // When statement ends with semicolon
            if (str_ends_with($trimmed, ';')) {
                try {
                    $pdo->exec($query);
                } catch (\Exception $e) {
                    // Ignore non-fatal drop or warning errors, re-throw only major breakages
                    if (!str_contains($e->getMessage(), 'Unknown table') && !str_contains($e->getMessage(), 'already exists')) {
                        Log::warning("SQL execution warning during PDO restore: " . $e->getMessage());
                    }
                }
                $query = '';
            }
        }

        fclose($handle);
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    }

    /**
     * Detect mysqldump binary in standard XAMPP or system PATH.
     */
    protected function findMysqldumpBinary(): ?string
    {
        $candidates = [
            env('DB_DUMP_PATH'),
            'c:\\xampp\\mysql\\bin\\mysqldump.exe',
            'c:\\laragon\\bin\\mysql\\current\\bin\\mysqldump.exe',
            'mysqldump'
        ];

        foreach ($candidates as $bin) {
            if (empty($bin)) continue;
            if (File::exists($bin)) {
                return $bin;
            }
        }

        return null;
    }

    /**
     * Detect mysql binary in standard XAMPP or system PATH.
     */
    protected function findMysqlBinary(): ?string
    {
        $candidates = [
            env('DB_MYSQL_PATH'),
            'c:\\xampp\\mysql\\bin\\mysql.exe',
            'c:\\laragon\\bin\\mysql\\current\\bin\\mysql.exe',
            'mysql'
        ];

        foreach ($candidates as $bin) {
            if (empty($bin)) continue;
            if (File::exists($bin)) {
                return $bin;
            }
        }

        return null;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    protected function logAudit(string $action, array $details): void
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id() ?? null,
                'action' => $action,
                'model_type' => 'DatabaseBackup',
                'model_id' => null,
                'old_values' => null,
                'new_values' => json_encode($details),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning("Could not write audit log for {$action}: " . $e->getMessage());
        }
    }
}
