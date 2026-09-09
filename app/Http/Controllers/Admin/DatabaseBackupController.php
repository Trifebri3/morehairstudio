<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = $this->backupService->listBackups();

        $totalSizeBytes = array_sum(array_column($backups, 'size_bytes'));
        $totalFiles = count($backups);
        $lastBackup = !empty($backups) ? $backups[0] : null;

        $totalSizeFormatted = $totalSizeBytes >= 1048576 
            ? round($totalSizeBytes / 1048576, 2) . ' MB' 
            : ($totalSizeBytes >= 1024 ? round($totalSizeBytes / 1024, 2) . ' KB' : $totalSizeBytes . ' B');

        return view('admin.database-backup', compact(
            'backups',
            'totalFiles',
            'totalSizeFormatted',
            'lastBackup'
        ));
    }

    public function create(Request $request)
    {
        $compress = $request->boolean('compress', false);

        $result = $this->backupService->createBackup($compress);

        if ($result['success']) {
            return redirect()->route('admin.database-backup')->with('success', $result['message']);
        }

        return redirect()->route('admin.database-backup')->with('error', $result['message']);
    }

    public function download(string $filename): BinaryFileResponse
    {
        $cleanFilename = basename($filename);
        $filePath = storage_path('app/backups/' . $cleanFilename);

        if (!file_exists($filePath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return response()->download($filePath, $cleanFilename);
    }

    public function destroy(string $filename)
    {
        $cleanFilename = basename($filename);
        $deleted = $this->backupService->deleteBackup($cleanFilename);

        if ($deleted) {
            return redirect()->route('admin.database-backup')->with('success', "File backup '{$cleanFilename}' berhasil dihapus.");
        }

        return redirect()->route('admin.database-backup')->with('error', "Gagal menghapus file backup '{$cleanFilename}'.");
    }

    public function restore(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'confirmation' => 'required|string'
        ]);

        if (trim(strtoupper($request->confirmation)) !== 'RESTORE') {
            return redirect()->route('admin.database-backup')->with('error', 'Konfirmasi keamanan tidak sesuai. Anda wajib mengetik "RESTORE" untuk melanjutkan.');
        }

        $result = $this->backupService->restoreBackup($request->filename);

        if ($result['success']) {
            return redirect()->route('admin.database-backup')->with('success', $result['message']);
        }

        return redirect()->route('admin.database-backup')->with('error', $result['message']);
    }
}
