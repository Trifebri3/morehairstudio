@extends('layouts.admin')

@section('page_title')
    WhatsApp Center
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-stone-200">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight uppercase">WhatsApp Communication Center</h1>
            <p class="text-xxs text-stone-500 font-bold uppercase tracking-wide mt-1">Kelola integrasi, otomasi event, dan riwayat pesan WhatsApp.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4 bg-white p-3.5 rounded-2xl border border-stone-150">
            <div class="flex items-center gap-2">
                <span class="text-xxs font-bold text-stone-600 uppercase">Aktifkan WhatsApp</span>
                <form method="POST" action="{{ route('admin.whatsapp.toggle') }}" id="toggle-whatsapp-form">
                    @csrf
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="whatsapp_enabled" value="1" onchange="document.getElementById('toggle-whatsapp-form').submit()" class="sr-only peer" {{ $whatsappEnabled ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0A3D91]"></div>
                    </label>
                </form>
            </div>
            <div class="h-6 w-px bg-stone-200"></div>
            <div class="flex items-center gap-2">
                <span class="text-xxs font-bold text-stone-600 uppercase">Koneksi:</span>
                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider 
                    @if($connectionStatus === 'Connected') bg-emerald-50 text-emerald-800 border border-emerald-100
                    @elseif($connectionStatus === 'Error') bg-red-50 text-red-800 border border-red-100
                    @else bg-stone-100 text-stone-600 border border-stone-150 @endif">
                    {{ $connectionStatus }}
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
        @foreach(['overview' => 'Overview', 'settings' => 'Konfigurasi API', 'templates' => 'Templates', 'automations' => 'Automations', 'contacts' => 'Kontak & Broadcast', 'logs' => 'Logs & Message History'] as $tabKey => $tabName)
            <a href="?tab={{ $tabKey }}" class="pb-3 text-xxs font-black uppercase tracking-wide transition-all border-b-2 
                {{ $activeTab === $tabKey ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
                {{ $tabName }}
            </a>
        @endforeach
    </div>

    <!-- Tab Contents -->
    @if($activeTab === 'overview')
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-stone-150 space-y-1.5">
                <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Kirim Hari Ini</span>
                <span class="text-xl font-black text-stone-900 font-mono">{{ $sentToday }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-stone-150 space-y-1.5">
                <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Sukses Terkirim</span>
                <span class="text-xl font-black text-emerald-600 font-mono">{{ $deliveredToday }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-stone-150 space-y-1.5">
                <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Pesan Dibaca</span>
                <span class="text-xl font-black text-[#0A3D91] font-mono">{{ $readToday }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-stone-150 space-y-1.5">
                <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Gagal Terkirim</span>
                <span class="text-xl font-black text-red-600 font-mono">{{ $failedToday }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Active Provider info -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Provider WhatsApp Aktif</h3>
                
                <div class="flex items-center justify-between p-4 bg-stone-50/50 rounded-xl border">
                    <div>
                        <span class="text-xxs font-black text-stone-750 uppercase">{{ $activeProvider === 'cloud_api' ? 'Meta Cloud API' : 'Fonnte Adapter' }}</span>
                        <span class="text-[9px] text-stone-400 block mt-0.5">Satu-satunya provider aktif pengiriman reservasi & broadcast saat ini.</span>
                    </div>
                    <span class="px-2 py-0.5 bg-[#0A3D91] text-white rounded text-[8px] font-bold uppercase tracking-wider">ACTIVE</span>
                </div>

                <div class="space-y-2 pt-2">
                    <span class="text-[10px] font-bold text-stone-500 uppercase block">Ganti Provider Aktif:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <form method="POST" action="{{ route('admin.whatsapp.switch', 'cloud_api') }}">
                            @csrf
                            <button type="submit" class="w-full h-9 rounded-xl border text-xxs font-bold transition {{ $activeProvider === 'cloud_api' ? 'bg-[#0A3D91] text-white border-transparent' : 'bg-white hover:bg-stone-50 border-stone-200 text-stone-750' }}">
                                Meta Cloud API
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.whatsapp.switch', 'fonnte') }}">
                            @csrf
                            <button type="submit" class="w-full h-9 rounded-xl border text-xxs font-bold transition {{ $activeProvider === 'fonnte' ? 'bg-[#0A3D91] text-white border-transparent' : 'bg-white hover:bg-stone-50 border-stone-200 text-stone-750' }}">
                                Fonnte Adapter
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Automation status -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Otomasi & Broadcast Stats</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border bg-stone-50/30">
                        <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Otomasi Aktif</span>
                        <span class="text-lg font-black text-stone-850 font-mono">{{ $automationsActive }} Aturan</span>
                    </div>
                    <div class="p-4 rounded-xl border bg-stone-50/30">
                        <span class="text-[9px] font-bold text-stone-400 uppercase tracking-wider block">Broadcast Berjalan</span>
                        <span class="text-lg font-black text-stone-850 font-mono">{{ $campaignsActive }} Kampanye</span>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'settings')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cloud API Form -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">WhatsApp Business Cloud API</h3>
                    <form method="POST" action="{{ route('admin.whatsapp.test', 'cloud_api') }}" class="inline">
                        @csrf
                        <input type="hidden" name="token" id="test_cloud_token">
                        <input type="hidden" name="phone_number_id" id="test_cloud_phone_id">
                        <input type="hidden" name="version" id="test_cloud_version">
                        <button type="submit" onclick="syncCloudTestFields()" class="text-[9px] font-black uppercase text-[#0A3D91] hover:underline">Test Koneksi</button>
                    </form>
                </div>
                
                <form method="POST" action="{{ route('admin.whatsapp.config.cloud') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Access Token (Secret)</label>
                            <input type="password" name="token" id="cloud_token_input" value="{{ $cloudToken }}" placeholder="Ketik token enkripsi baru" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Phone Number ID</label>
                            <input type="text" name="phone_number_id" id="cloud_phone_id_input" value="{{ $cloudPhoneId }}" placeholder="e.g. 1092837372..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Graph API Version</label>
                            <input type="text" name="version" id="cloud_version_input" value="{{ $cloudVersion }}" placeholder="v20.0" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-4 h-8 bg-stone-900 hover:bg-stone-850 text-white rounded-lg text-xxs font-bold uppercase tracking-wider transition">
                            Simpan Kredensial Cloud API
                        </button>
                    </div>
                </form>
            </div>

            <!-- Fonnte Configuration Form -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Fonnte API Adapter</h3>
                    <form method="POST" action="{{ route('admin.whatsapp.test', 'fonnte') }}" class="inline">
                        @csrf
                        <input type="hidden" name="token" id="test_fonnte_token">
                        <button type="submit" onclick="syncFonnteTestFields()" class="text-[9px] font-black uppercase text-[#0A3D91] hover:underline">Test Koneksi</button>
                    </form>
                </div>
                
                <form method="POST" action="{{ route('admin.whatsapp.config.fonnte') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">API Token (Secret)</label>
                            <input type="password" name="token" id="fonnte_token_input" value="{{ $fonnteToken }}" placeholder="Ketik token enkripsi fonnte baru" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-4 h-8 bg-stone-900 hover:bg-stone-850 text-white rounded-lg text-xxs font-bold uppercase tracking-wider transition">
                            Simpan Kredensial Fonnte
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @elseif($activeTab === 'templates')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Create template form -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 h-fit">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Tambah Template Baru</h3>
                
                <form method="POST" action="{{ route('admin.whatsapp.template.create') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Nama Template</label>
                            <input type="text" name="template_name" placeholder="booking_confirmation" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" required />
                            <x-input-error :messages="$errors->get('template_name')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Bahasa</label>
                            <select name="language" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                                <option value="id">Indonesia (id)</option>
                                <option value="en">English (en)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Isi Pesan (Body)</label>
                            <textarea name="body" rows="4" placeholder="Halo @{{customer_name}}, sesi reservasi Anda di @{{outlet_name}} telah dikonfirmasi." class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition" required></textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Lampiran File (Opsional)</label>
                            <input type="file" name="file" class="w-full text-xs text-stone-500 file:mr-4 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-stone-100 file:text-stone-750 hover:file:bg-stone-200 transition" />
                            <x-input-error :messages="$errors->get('file')" class="mt-1" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                            Buat Template
                        </button>
                    </div>
                </form>
            </div>

            <!-- Templates List -->
            <div class="md:col-span-2 bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Daftar Template Tersimpan</h3>
                
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse($templates as $t)
                        <div class="p-4 rounded-xl border bg-stone-50/30 flex justify-between items-start gap-4">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xxs font-black text-stone-900 uppercase font-mono">{{ $t->template_name }}</span>
                                    <span class="px-1.5 py-0.2 bg-stone-100 border text-stone-500 text-[8px] font-bold uppercase rounded">{{ $t->language }}</span>
                                </div>
                                <p class="text-xxs text-stone-600 whitespace-pre-line font-mono">{{ $t->body }}</p>
                                @if(!empty($t->file_path))
                                    <div class="flex items-center gap-1 text-[9px] text-[#0A3D91] font-bold mt-1.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <span>File Terlampir: {{ basename($t->file_path) }}</span>
                                    </div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.whatsapp.template.delete', $t->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[9px] font-bold uppercase text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xxs text-stone-400 font-bold text-center py-6">Belum ada template tersimpan.</p>
                    @endforelse
                </div>
            </div>
        </div>

    @elseif($activeTab === 'automations')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Create Automation form -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 h-fit">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Tambah Otomasi Event</h3>
                
                <form method="POST" action="{{ route('admin.whatsapp.automation.create') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Nama Otomasi</label>
                            <input type="text" name="name" placeholder="Notifikasi Konfirmasi Reservasi" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Event Pemicu (Trigger)</label>
                            <select name="event_type" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                                <option value="BOOKING_CREATED">Booking Created</option>
                                <option value="BOOKING_CONFIRMED">Booking Confirmed</option>
                                <option value="BOOKING_CANCELLED">Booking Cancelled</option>
                                <option value="BOOKING_COMPLETED">Booking Completed</option>
                                <option value="CUSTOMER_CREATED">Customer Created</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Gunakan Template</label>
                            <select name="template_name" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" required>
                                <option value="">-- Pilih Template --</option>
                                @foreach($templates as $temp)
                                    <option value="{{ $temp->template_name }}">{{ $temp->template_name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('template_name')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Delay Waktu (Menit)</label>
                            <input type="number" name="delay_minutes" value="0" min="0" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                            <x-input-error :messages="$errors->get('delay_minutes')" class="mt-1" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                            Buat Aturan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Automations List -->
            <div class="md:col-span-2 bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Aturan Otomasi Terjadwal</h3>
                
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse($automations as $a)
                        <div class="p-4 rounded-xl border bg-stone-50/30 flex justify-between items-center gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xxs font-black text-stone-900 uppercase">{{ $a->name }}</span>
                                    <span class="px-1.5 py-0.2 bg-blue-50 border border-blue-100 text-[#0A3D91] text-[8px] font-bold uppercase rounded">{{ $a->event_type }}</span>
                                </div>
                                <div class="text-[9px] text-stone-500 font-medium">
                                    Template: <span class="font-mono">{{ $a->template_name }}</span> | Delay: {{ $a->delay_minutes }} menit
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.whatsapp.automation.delete', $a->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus otomasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[9px] font-bold uppercase text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xxs text-stone-400 font-bold text-center py-6">Belum ada aturan otomasi dibuat.</p>
                    @endforelse
                </div>
            </div>
        </div>

    @elseif($activeTab === 'logs')
        <!-- Logs Table -->
        <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
            <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Riwayat Pengiriman Pesan</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xxs font-bold text-stone-600">
                    <thead>
                        <tr class="border-b text-stone-450 uppercase text-[9px] tracking-wider">
                            <th class="py-3">Waktu</th>
                            <th>Penerima</th>
                            <th>Layanan</th>
                            <th>Provider</th>
                            <th>Isi Pesan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($logs as $log)
                            <tr>
                                <td class="py-3 text-stone-450 font-mono">{{ $log->created_at->format('d/m H:i') }}</td>
                                <td class="font-mono">{{ $log->recipient }}</td>
                                <td class="uppercase">{{ $log->message_type }}</td>
                                <td class="uppercase">{{ $log->provider }}</td>
                                <td class="max-w-xs truncate font-mono font-normal text-stone-500">{{ $log->body }}</td>
                                <td>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider 
                                        @if($log->status === 'SENT' || $log->status === 'DELIVERED' || $log->status === 'READ') bg-emerald-50 text-emerald-800 border border-emerald-100
                                        @else bg-red-50 text-red-800 border border-red-100 @endif">
                                        {{ $log->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-stone-400">Belum ada log pesan terkirim.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($activeTab === 'contacts')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Sidebar: Import Contacts -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 h-fit">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Impor Kontak CSV</h3>
                
                <form method="POST" action="{{ route('admin.whatsapp.import') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Pilih File CSV</label>
                            <input type="file" name="file" class="w-full text-xs text-stone-500 file:mr-4 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-stone-100 file:text-stone-750 hover:file:bg-stone-200 transition" required />
                            <span class="text-[8px] text-stone-400 block mt-1">Format file harus memuat kolom "name" dan "phone" (format internasional, e.g. 62812xxx).</span>
                            <x-input-error :messages="$errors->get('file')" class="mt-1" />
                        </div>
                        
                        <button type="submit" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                            Mulai Impor Kontak
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Panel: CRM Contacts List -->
            <div class="md:col-span-2 bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Kontak Pelanggan (CRM)</h3>
                    
                    <!-- Search & Filter fields -->
                    <form method="GET" action="{{ route('admin.whatsapp') }}" class="flex gap-2">
                        <input type="hidden" name="tab" value="contacts">
                        <input type="text" name="search" value="{{ $searchContact }}" placeholder="Cari nama / nomor..." class="text-xxs font-bold rounded-lg border-stone-200 bg-stone-50/50 h-7 px-3 text-stone-750 focus:border-[#0A3D91] transition w-40" />
                        <button type="submit" class="px-3 py-1 bg-stone-100 hover:bg-stone-200 border rounded-lg text-xxs font-bold text-stone-750">Cari</button>
                    </form>
                </div>

                <!-- Bulk action indicator bar (JS Controlled) -->
                <div id="bulk-bar" class="hidden p-3 bg-blue-50 border border-blue-100 rounded-xl flex justify-between items-center">
                    <span id="bulk-count-label" class="text-xxs font-bold text-[#0A3D91]">0 Kontak Terpilih</span>
                    <div class="flex gap-2">
                        <button type="button" onclick="openBulkModal()" class="px-3 h-6 bg-[#0A3D91] text-white rounded-lg text-[9px] font-black uppercase tracking-wider">
                            Kirim Pesan Massal
                        </button>
                        <button type="button" onclick="deselectAllContacts()" class="px-3 h-6 bg-stone-200 hover:bg-stone-250 text-stone-700 rounded-lg text-[9px] font-black uppercase tracking-wider">
                            Batal Pilih
                        </button>
                    </div>
                </div>

                <!-- Contacts Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xxs font-bold text-stone-600">
                        <thead>
                            <tr class="border-b text-stone-450 uppercase text-[9px] tracking-wider">
                                <th class="py-2.5 w-8">
                                    <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)" class="rounded border-stone-300 text-[#0A3D91] focus:ring-[#0A3D91]" />
                                </th>
                                <th>Nama Pelanggan</th>
                                <th>WhatsApp</th>
                                <th>CRM Tags</th>
                                <th class="text-center">Total Booking</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($contacts as $contact)
                                <tr class="hover:bg-stone-50/20">
                                    <td class="py-2.5">
                                        <input type="checkbox" name="selected_contacts[]" value="{{ $contact->id }}" onchange="onContactSelectChange()" class="contact-checkbox rounded border-stone-300 text-[#0A3D91] focus:ring-[#0A3D91]" />
                                    </td>
                                    <td>
                                        <div class="font-black text-stone-805">{{ $contact->name }}</div>
                                        <div class="text-[9px] text-stone-400 font-mono">{{ $contact->customer_code }}</div>
                                    </td>
                                    <td class="font-mono text-stone-550">{{ $contact->phone }}</td>
                                    <td>
                                        @if(!empty($contact->tags))
                                            @foreach((array)$contact->tags as $tag)
                                                <span class="px-1.5 py-0.2 bg-stone-100 border text-stone-500 text-[8px] font-bold uppercase rounded">{{ $tag }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-stone-300">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-mono">{{ $contact->bookings_count }}</td>
                                    <td class="text-right">
                                        <button type="button" onclick="openSingleModal('{{ $contact->id }}', '{{ $contact->name }}', '{{ $contact->phone }}')" class="text-[9px] font-black uppercase text-[#0A3D91] hover:underline">
                                            Kirim Pesan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-stone-400">Belum ada kontak terdaftar di CRM.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pt-3 border-t">
                    {{ $contacts->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Single Send Modal (Vanilla JS Popup) -->
    <div id="singleModal" class="hidden fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-stone-150 shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Kirim Pesan WhatsApp</h3>
                <button type="button" onclick="closeSingleModal()" class="text-stone-400 hover:text-stone-700 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.whatsapp.send.single') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="phone" id="singleRecipientPhone">
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-0.5">Penerima</label>
                        <div class="text-xs font-black text-stone-800" id="singleRecipientLabel">-</div>
                    </div>
                    
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Gunakan Template (Opsional)</label>
                        <select id="singleTemplateSelector" onchange="applySingleTemplate(this.value)" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                            <option value="">-- Tulis Pesan Kustom --</option>
                            @foreach($templates as $temp)
                                <option value="{{ $temp->template_name }}" data-body="{{ $temp->body }}">{{ $temp->template_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Isi Pesan</label>
                        <textarea name="message" id="singleMessageTextarea" rows="5" placeholder="Tulis isi pesan Anda di sini..." class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition" required></textarea>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Kirim Sekarang
                    </button>
                    <button type="button" onclick="closeSingleModal()" class="px-4 h-9 bg-stone-100 hover:bg-stone-150 text-stone-700 rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Send Modal (Vanilla JS Popup) -->
    <div id="bulkModal" class="hidden fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-stone-150 shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Kirim Pesan Massal (Broadcast)</h3>
                <button type="button" onclick="closeBulkModal()" class="text-stone-400 hover:text-stone-700 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.whatsapp.send.bulk') }}" class="space-y-4" id="bulk-send-form">
                @csrf
                <div id="bulk-hidden-container"></div>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-0.5">Total Target Penerima</label>
                        <div class="text-xs font-black text-[#0A3D91]" id="bulkTargetLabel">0 Kontak terpilih</div>
                    </div>
                    
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Gunakan Template (Opsional)</label>
                        <select id="bulkTemplateSelector" onchange="applyBulkTemplate(this.value)" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                            <option value="">-- Tulis Pesan Kustom --</option>
                            @foreach($templates as $temp)
                                <option value="{{ $temp->template_name }}" data-body="{{ $temp->body }}">{{ $temp->template_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Isi Pesan</label>
                        <textarea name="message" id="bulkMessageTextarea" rows="5" placeholder="Tulis pesan siaran massal..." class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition" required></textarea>
                        <span class="text-[8px] text-stone-400 block mt-1">Gunakan tag @{{customer_name}} untuk menyisipkan nama pelanggan secara dinamis.</span>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 h-9 bg-[#0A3D91] text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Mulai Broadcast
                    </button>
                    <button type="button" onclick="closeBulkModal()" class="px-4 h-9 bg-stone-100 hover:bg-stone-150 text-stone-700 rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Synchronize current input values to the hidden test connection forms
    function syncCloudTestFields() {
        document.getElementById('test_cloud_token').value = document.getElementById('cloud_token_input').value;
        document.getElementById('test_cloud_phone_id').value = document.getElementById('cloud_phone_id_input').value;
        document.getElementById('test_cloud_version').value = document.getElementById('cloud_version_input').value;
    }

    function syncFonnteTestFields() {
        document.getElementById('test_fonnte_token').value = document.getElementById('fonnte_token_input').value;
    }

    // Modal helpers
    var activeRecipientName = '';
    function openSingleModal(id, name, phone) {
        activeRecipientName = name;
        document.getElementById('singleRecipientPhone').value = phone;
        document.getElementById('singleRecipientLabel').innerText = name + ' (' + phone + ')';
        document.getElementById('singleTemplateSelector').value = '';
        document.getElementById('singleMessageTextarea').value = '';
        document.getElementById('singleModal').classList.remove('hidden');
    }

    function closeSingleModal() {
        document.getElementById('singleModal').classList.add('hidden');
    }

    function applySingleTemplate(val) {
        if (!val) {
            document.getElementById('singleMessageTextarea').value = '';
            return;
        }
        var selector = document.getElementById('singleTemplateSelector');
        var opt = selector.options[selector.selectedIndex];
        var body = opt.getAttribute('data-body') || '';
        
        // Dynamically replace variables for current recipient
        body = body.replace('{{customer_name}}', activeRecipientName);
        body = body.replace('{{booking_code}}', 'DIRECT');
        document.getElementById('singleMessageTextarea').value = body;
    }

    // Bulk selection helpers
    function toggleSelectAll(master) {
        var checkboxes = document.querySelectorAll('.contact-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = master.checked;
        });
        onContactSelectChange();
    }

    function onContactSelectChange() {
        var checked = document.querySelectorAll('.contact-checkbox:checked');
        var bulkBar = document.getElementById('bulk-bar');
        var bulkCountLabel = document.getElementById('bulk-count-label');
        
        if (checked.length > 0) {
            bulkBar.classList.remove('hidden');
            bulkCountLabel.innerText = checked.length + ' Kontak Terpilih';
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    function deselectAllContacts() {
        document.getElementById('select-all-checkbox').checked = false;
        var checkboxes = document.querySelectorAll('.contact-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = false;
        });
        onContactSelectChange();
    }

    function openBulkModal() {
        var checked = document.querySelectorAll('.contact-checkbox:checked');
        if (checked.length === 0) return;

        var container = document.getElementById('bulk-hidden-container');
        container.innerHTML = '';
        
        checked.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'customer_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkTargetLabel').innerText = checked.length + ' Kontak terpilih dari CRM';
        document.getElementById('bulkTemplateSelector').value = '';
        document.getElementById('bulkMessageTextarea').value = '';
        document.getElementById('bulkModal').classList.remove('hidden');
    }

    function closeBulkModal() {
        document.getElementById('bulkModal').classList.add('hidden');
    }

    function applyBulkTemplate(val) {
        if (!val) {
            document.getElementById('bulkMessageTextarea').value = '';
            return;
        }
        var selector = document.getElementById('bulkTemplateSelector');
        var opt = selector.options[selector.selectedIndex];
        var body = opt.getAttribute('data-body') || '';
        document.getElementById('bulkMessageTextarea').value = body;
    }
</script>
@endsection
