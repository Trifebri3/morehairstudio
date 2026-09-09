@extends('layouts.admin')

@section('content')
<div class="space-y-6 pb-12" x-data="{ restoreModalOpen: false, selectedFile: '' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-stone-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                    Disaster Recovery & Data Safety
                </span>
            </div>
            <h1 class="text-2xl font-black text-stone-900 tracking-tight mt-1">Backup & Restore Database</h1>
            <p class="text-xs text-stone-500 mt-1">
                Pencadangan database sistem MORE Hair Studio secara otomatis dan manual dengan proteksi pemulihan data menyeluruh.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.database-research') }}" class="px-3.5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Lihat Riset Database &rarr;</span>
            </a>

            <!-- Manual Backup Form -->
            <form action="{{ route('admin.database-backup.create') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <label class="hidden sm:flex items-center gap-1.5 text-xs text-stone-600 font-medium cursor-pointer">
                    <input type="checkbox" name="compress" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500">
                    <span>Gzip (.gz)</span>
                </label>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Backup Sekarang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Flash Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- KPI Status Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Jadwal Otomatis</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            </div>
            <p class="text-base font-black text-stone-900 mt-2">Harian (02:00 WIB)</p>
            <p class="text-[11px] text-emerald-600 font-bold mt-1">Status: Aktif & Terjadwal</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Total File Tersimpan</span>
                <span class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 flex items-center justify-center font-bold text-xs">SQL</span>
            </div>
            <p class="text-2xl font-black text-stone-900 mt-2">{{ $totalFiles }} <span class="text-xs font-normal text-stone-500">File</span></p>
            <p class="text-[11px] text-stone-500 mt-1">Retensi otomatis: <span class="font-bold text-stone-700">7 Hari</span></p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Penggunaan Disk</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">MB</span>
            </div>
            <p class="text-2xl font-black text-stone-900 mt-2">{{ $totalSizeFormatted }}</p>
            <p class="text-[11px] text-stone-500 mt-1">Lokasi: <span class="font-mono text-stone-700 text-[10px]">storage/app/backups</span></p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Backup Terakhir</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">OK</span>
            </div>
            <p class="text-sm font-black text-stone-900 mt-2 truncate">
                {{ $lastBackup ? $lastBackup['relative_age'] : 'Belum Ada' }}
            </p>
            <p class="text-[11px] text-stone-500 mt-1 font-mono">
                {{ $lastBackup ? $lastBackup['created_at'] : '-' }}
            </p>
        </div>
    </div>

    <!-- Backup Files Table -->
    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-2xs">
        <div class="p-4 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-stone-900">Riwayat File Backup Database</h3>
                <p class="text-xs text-stone-500">File dump database lengkap (struktur DDL tabel + data DML baris).</p>
            </div>
            <span class="text-xs font-mono font-bold bg-stone-100 px-3 py-1 rounded-lg text-stone-600">
                Total {{ count($backups) }} Backup
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 text-stone-500 font-bold border-b border-stone-200 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Nama File Backup</th>
                        <th class="py-3 px-4">Format / Tipe</th>
                        <th class="py-3 px-4 text-right">Ukuran File</th>
                        <th class="py-3 px-4">Waktu Pembuatan</th>
                        <th class="py-3 px-4 text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($backups as $b)
                    <tr class="hover:bg-stone-50/70 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="font-mono font-bold text-stone-900">{{ $b['filename'] }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $b['is_compressed'] ? 'bg-indigo-100 text-indigo-800' : 'bg-stone-100 text-stone-700' }}">
                                {{ $b['is_compressed'] ? 'SQL Compressed (Gzip)' : 'Raw SQL Dump' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-right text-stone-800">
                            {{ $b['size_formatted'] }}
                        </td>
                        <td class="py-3 px-4 text-stone-600">
                            <span class="block font-mono text-[11px]">{{ $b['created_at'] }}</span>
                            <span class="text-[10px] text-stone-400">({{ $b['relative_age'] }})</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Download Button -->
                                <a href="{{ route('admin.database-backup.download', $b['filename']) }}" class="px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="Unduh ke Komputer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh</span>
                                </a>

                                <!-- Restore Button (Triggers modal) -->
                                <button type="button" @click="selectedFile = '{{ $b['filename'] }}'; restoreModalOpen = true" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer" title="Pulihkan Database dari File Ini">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Restore</span>
                                </button>

                                <!-- Delete Button -->
                                <form action="{{ route('admin.database-backup.destroy', $b['filename']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file backup ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-stone-400 hover:text-rose-600 rounded-lg transition" title="Hapus File Backup">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-stone-400">
                            <svg class="w-8 h-8 mx-auto text-stone-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7c0-2 1-3 3-3h10c2 0 3 1 3 3M4 7h16"/></svg>
                            <p class="font-bold text-stone-600">Belum ada file backup database yang dibuat.</p>
                            <p class="text-xs text-stone-400 mt-1">Klik tombol "Backup Sekarang" di atas untuk membuat cadangan data pertama Anda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Security & Disaster Recovery SOP Notice -->
    <div class="p-4 bg-stone-50 border border-stone-200 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-stone-200 text-stone-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                i
            </div>
            <div>
                <h4 class="text-xs font-bold text-stone-900 uppercase tracking-wider">Prosedur Keamanan & Retensi Data</h4>
                <p class="text-xs text-stone-600 mt-0.5">
                    Cadangan database dijalankan otomatis setiap hari pukul 02:00 WIB melalui background scheduler Laravel. File disimpan terisolasi di server dan di-prune otomatis setelah 7 hari. Unduh cadangan secara berkala ke harddisk eksternal untuk pemulihan bencana maksimal.
                </p>
            </div>
        </div>
    </div>

    <!-- RESTORE CONFIRMATION MODAL -->
    <div x-show="restoreModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" style="display: none;" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-stone-200" @click.away="restoreModalOpen = false">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-stone-900">Konfirmasi Pemulihan Database</h3>
                    <p class="text-xs text-stone-500">Tindakan ini akan menimpa seluruh database dengan isi file backup.</p>
                </div>
            </div>

            <div class="p-3 bg-stone-50 border border-stone-200 rounded-xl text-xs font-mono text-stone-800 break-all">
                File: <strong x-text="selectedFile"></strong>
            </div>

            <form action="{{ route('admin.database-backup.restore') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="filename" :value="selectedFile">

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">
                        Ketik kata <span class="text-rose-600 font-mono">RESTORE</span> untuk mengonfirmasi:
                    </label>
                    <input type="text" name="confirmation" required placeholder="RESTORE" class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-mono font-bold uppercase focus:outline-none focus:border-rose-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="restoreModalOpen = false" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs">
                        Pulihkan Database Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
