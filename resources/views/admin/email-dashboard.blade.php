@extends('layouts.admin')

@section('page_title')
    Email Communication Center
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-stone-200">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight uppercase">Email Communication Center</h1>
            <p class="text-xxs text-stone-500 font-bold uppercase tracking-wide mt-1">Kelola integrasi SMTP, template email, dan riwayat pesan email transaksional.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4 bg-white p-3.5 rounded-2xl border border-stone-150">
            <form method="POST" action="{{ route('admin.email.toggle') }}" class="flex items-center gap-2">
                @csrf
                <span class="text-xxs font-bold text-stone-600 uppercase">Aktifkan Saluran Email</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="emailEnabled" value="1" {{ $emailEnabled ? 'checked' : '' }} onchange="this.form.submit()" class="sr-only peer">
                    <div class="w-9 h-5 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0A3D91]"></div>
                </label>
            </form>
            <div class="h-6 w-px bg-stone-200"></div>
            <div class="flex items-center gap-2">
                <span class="text-xxs font-bold text-stone-600 uppercase">Status SMTP:</span>
                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider 
                    @if($isActive) bg-emerald-50 text-emerald-800 border border-emerald-100
                    @else bg-stone-100 text-stone-600 border border-stone-150 @endif">
                    {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session()->has('message'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-xxs font-bold">
            {{ session('message') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="p-3.5 bg-red-50 border border-red-100 text-red-800 rounded-xl text-xxs font-bold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="flex border-b border-stone-200 gap-6">
        @foreach(['overview' => 'Overview', 'settings' => 'Konfigurasi SMTP', 'templates' => 'Email Templates', 'logs' => 'Logs & History'] as $tabKey => $tabName)
            <a href="?tab={{ $tabKey }}" class="pb-3 text-xxs font-black uppercase tracking-wide transition-all border-b-2 
                {{ $activeTab === $tabKey ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
                {{ $tabName }}
            </a>
        @endforeach
    </div>

    <!-- Tab Contents -->
    @if($activeTab === 'overview')
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-stone-150 space-y-1.5">
                <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Kirim Hari Ini</span>
                <span class="text-xl font-black text-stone-900 font-mono">{{ $sentCount }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-stone-150 space-y-1.5">
                <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Sukses Terkirim</span>
                <span class="text-xl font-black text-emerald-600 font-mono">{{ $sentCount }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-stone-150 space-y-1.5">
                <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Gagal Terkirim</span>
                <span class="text-xl font-black text-red-600 font-mono">{{ $failedCount }}</span>
            </div>
        </div>

    @elseif($activeTab === 'settings')
        <form method="POST" action="{{ route('admin.email.config') }}" class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 max-w-xl">
            @csrf
            <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Konfigurasi Server SMTP</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Mail Host</label>
                    <input type="text" name="host" value="{{ old('host', $host) }}" placeholder="smtp.mailtrap.io" required class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    <x-input-error :messages="$errors->get('host')" class="mt-1" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Mail Port</label>
                    <input type="number" name="port" value="{{ old('port', $port) }}" placeholder="587" required class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    <x-input-error :messages="$errors->get('port')" class="mt-1" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username', $username) }}" placeholder="user_smtp" required class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Password (Secret)</label>
                    <input type="password" name="password" value="{{ old('password', $password) }}" placeholder="••••••••" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Encryption</label>
                    <select name="encryption" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                        <option value="tls" {{ old('encryption', $encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('encryption', $encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ old('encryption', $encryption) === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">From Email Address</label>
                    <input type="email" name="from_address" value="{{ old('from_address', $fromAddress) }}" placeholder="no-reply@morehair.com" required class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    <x-input-error :messages="$errors->get('from_address')" class="mt-1" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">From Sender Name</label>
                    <input type="text" name="from_name" value="{{ old('from_name', $fromName) }}" placeholder="More Hair Studio" required class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    <x-input-error :messages="$errors->get('from_name')" class="mt-1" />
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between">
                <label class="flex items-center space-x-2 text-xxs font-bold text-stone-600 uppercase select-none">
                    <input type="checkbox" name="is_active" value="1" {{ $isActive ? 'checked' : '' }} class="rounded border-stone-300 text-[#0A3D91] focus:ring-[#0A3D91]">
                    <span>Aktifkan Server SMTP</span>
                </label>
                <button type="submit" class="px-4 h-8 bg-stone-900 hover:bg-stone-850 text-white rounded-lg text-xxs font-bold uppercase tracking-wider transition">
                    Simpan Konfigurasi SMTP
                </button>
            </div>
        </form>

    @elseif($activeTab === 'templates')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Create Template -->
            <form method="POST" action="{{ route('admin.email.template') }}" class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 h-fit">
                @csrf
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Tambah Template Email</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Nama Template</label>
                        <input type="text" name="name" value="{{ old('name', 'email_booking_confirm') }}" placeholder="email_booking_confirm" required class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Subjek Email</label>
                        <input type="text" name="subject" value="{{ old('subject', 'Konfirmasi Pemesanan - More Hair Studio') }}" placeholder="Konfirmasi Pemesanan - More Hair Studio" required class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Body HTML</label>
                        <textarea name="body" rows="5" placeholder="<p>Halo @{{customer_name}}, reservasi anda terkonfirmasi.</p>" required class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition">{{ old('body', '<p>Halo {{customer_name}}, reservasi anda terkonfirmasi.</p>') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-1" />
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Buat Template Email
                    </button>
                </div>
            </form>

            <!-- List Templates -->
            <div class="md:col-span-2 bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Template Email Tersimpan</h3>
                
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse($templates as $t)
                        <div class="p-4 rounded-xl border bg-stone-50/30 flex justify-between items-start gap-4">
                            <div class="space-y-1.5">
                                <span class="text-xxs font-black text-stone-900 uppercase font-mono">{{ $t->name }}</span>
                                <div class="text-[10px] text-stone-600 font-bold">Subjek: {{ $t->subject }}</div>
                                <p class="text-xxs text-stone-500 font-normal truncate max-w-lg">{{ strip_tags($t->body) }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.email.delete-template', $t->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[9px] font-bold uppercase text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xxs text-stone-400 font-bold text-center py-6">Belum ada template email tersimpan.</p>
                    @endforelse
                </div>
            </div>
        </div>

    @elseif($activeTab === 'logs')
        <!-- Logs Table -->
        <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
            <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Riwayat Pengiriman Email</h3>
            
            <div class="overflow-x-auto rounded-xl border border-stone-200">
                <table class="w-full text-left text-xxs font-bold text-stone-600 border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 uppercase text-[9px] tracking-wider">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Penerima Email</th>
                            <th class="py-3 px-4">Subjek</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Error Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($logs as $log)
                            <tr class="hover:bg-stone-50/50 transition">
                                <td class="py-3 px-4 text-stone-400 font-mono">{{ $log->created_at->format('d/m H:i') }}</td>
                                <td class="py-3 px-4 font-mono">{{ $log->recipient }}</td>
                                <td class="py-3 px-4 text-stone-800">{{ $log->subject }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider 
                                        @if($log->status === 'SENT') bg-emerald-50 text-emerald-800 border border-emerald-100
                                        @else bg-red-50 text-red-800 border border-red-100 @endif">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[9px] font-normal text-stone-400 max-w-xs truncate">{{ $log->error_message ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-stone-400">Belum ada log email terkirim.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
