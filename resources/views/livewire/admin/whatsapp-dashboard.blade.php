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
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="whatsappEnabled" wire:change="toggleChannel" class="sr-only peer">
                    <div class="w-9 h-5 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0A3D91]"></div>
                </label>
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
            <button wire:click="$set('activeTab', '{{ $tabKey }}')" class="pb-3 text-xxs font-black uppercase tracking-wide transition-all border-b-2 
                {{ $activeTab === $tabKey ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
                {{ $tabName }}
            </button>
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
                        <button wire:click="switchProvider('cloud_api')" class="h-9 rounded-xl border text-xxs font-bold transition {{ $activeProvider === 'cloud_api' ? 'bg-[#0A3D91] text-white border-transparent' : 'bg-white hover:bg-stone-50 border-stone-200 text-stone-750' }}">
                            Meta Cloud API
                        </button>
                        <button wire:click="switchProvider('fonnte')" class="h-9 rounded-xl border text-xxs font-bold transition {{ $activeProvider === 'fonnte' ? 'bg-[#0A3D91] text-white border-transparent' : 'bg-white hover:bg-stone-50 border-stone-200 text-stone-750' }}">
                            Fonnte Adapter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Campaign status -->
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
                    <button type="button" wire:click="testConnection('cloud_api')" class="text-[9px] font-black uppercase text-[#0A3D91] hover:underline">Test Koneksi</button>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Access Token (Secret)</label>
                        <input type="password" wire:model="cloudToken" placeholder="Ketik token enkripsi baru" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Phone Number ID</label>
                        <input type="text" wire:model="cloudPhoneId" placeholder="e.g. 1092837372..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Graph API Version</label>
                        <input type="text" wire:model="cloudVersion" placeholder="v20.0" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </div>
                </div>

                <div class="pt-2">
                    <button wire:click="saveCloudConfig" class="px-4 h-8 bg-stone-900 hover:bg-stone-850 text-white rounded-lg text-xxs font-bold uppercase tracking-wider transition">
                        Simpan Kredensial Cloud API
                    </button>
                </div>
            </div>

            <!-- Fonnte Configuration Form -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Fonnte API Adapter</h3>
                    <button type="button" wire:click="testConnection('fonnte')" class="text-[9px] font-black uppercase text-[#0A3D91] hover:underline">Test Koneksi</button>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">API Token (Secret)</label>
                        <input type="password" wire:model="fonnteToken" placeholder="Ketik token enkripsi fonnte baru" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </div>
                </div>

                <div class="pt-2">
                    <button wire:click="saveFonnteConfig" class="px-4 h-8 bg-stone-900 hover:bg-stone-850 text-white rounded-lg text-xxs font-bold uppercase tracking-wider transition">
                        Simpan Kredensial Fonnte
                    </button>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'templates')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Create template form -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 h-fit">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Tambah Template Baru</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Nama Template</label>
                        <input type="text" wire:model="tempName" placeholder="booking_confirmation" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        @error('tempName') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Bahasa</label>
                        <select wire:model="tempLanguage" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                            <option value="id">Indonesia (id)</option>
                            <option value="en">English (en)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Isi Pesan (Body)</label>
                        <textarea wire:model="tempBody" rows="4" placeholder="Halo @{{customer_name}}, sesi reservasi Anda di @{{outlet_name}} telah dikonfirmasi." class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition"></textarea>
                        @error('tempBody') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Lampiran File (Opsional)</label>
                        <input type="file" wire:model="tempFile" class="w-full text-xs text-stone-500 file:mr-4 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-stone-100 file:text-stone-750 hover:file:bg-stone-200 transition" />
                        @error('tempFile') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button wire:click="createTemplate" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Buat Template
                    </button>
                </div>
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
                            <button wire:click="deleteTemplate({{ $t->id }})" class="text-[9px] font-bold uppercase text-red-600 hover:underline">Hapus</button>
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
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Nama Otomasi</label>
                        <input type="text" wire:model="autoName" placeholder="Notifikasi Konfirmasi Reservasi" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        @error('autoName') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Event Pemicu (Trigger)</label>
                        <select wire:model="autoEvent" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                            <option value="BOOKING_CREATED">Booking Created</option>
                            <option value="BOOKING_CONFIRMED">Booking Confirmed</option>
                            <option value="BOOKING_CANCELLED">Booking Cancelled</option>
                            <option value="BOOKING_COMPLETED">Booking Completed</option>
                            <option value="CUSTOMER_CREATED">Customer Created</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Gunakan Template</label>
                        <select wire:model="autoTemplate" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                            <option value="">-- Pilih Template --</option>
                            @foreach($templates as $temp)
                                <option value="{{ $temp->template_name }}">{{ $temp->template_name }}</option>
                            @endforeach
                        </select>
                        @error('autoTemplate') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Delay Waktu (Menit)</label>
                        <input type="number" wire:model="autoDelay" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        @error('autoDelay') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button wire:click="createAutomation" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Buat Aturan
                    </button>
                </div>
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
                            <button wire:click="deleteAutomation({{ $a->id }})" class="text-[9px] font-bold uppercase text-red-600 hover:underline">Hapus</button>
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
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Pilih File CSV</label>
                        <input type="file" wire:model="csvFile" class="w-full text-xs text-stone-500 file:mr-4 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-stone-100 file:text-stone-750 hover:file:bg-stone-200 transition" />
                        <span class="text-[8px] text-stone-400 block mt-1">Format file harus memuat kolom "name" dan "phone" (format internasional, e.g. 62812xxx).</span>
                        @error('csvFile') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <button wire:click="importContacts" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Mulai Impor Kontak
                    </button>
                </div>
            </div>

            <!-- Right Panel: CRM Contacts List -->
            <div class="md:col-span-2 bg-white p-5 rounded-2xl border border-stone-150 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Kontak Pelanggan (CRM)</h3>
                    
                    <!-- Search & Filter fields -->
                    <div class="flex gap-2">
                        <input type="text" wire:model.live.debounce.300ms="searchContact" placeholder="Cari nama / nomor..." class="text-xxs font-bold rounded-lg border-stone-200 bg-stone-50/50 h-7 px-3 text-stone-750 focus:border-[#0A3D91] transition w-40" />
                    </div>
                </div>

                <!-- Bulk action indicator bar -->
                @if(count($selectedCustomerIds) > 0)
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl flex justify-between items-center">
                        <span class="text-xxs font-bold text-[#0A3D91]">{{ count($selectedCustomerIds) }} Kontak Terpilih</span>
                        <div class="flex gap-2">
                            <button wire:click="openBulkModal" class="px-3 h-6 bg-[#0A3D91] text-white rounded-lg text-[9px] font-black uppercase tracking-wider">
                                Kirim Pesan Massal
                            </button>
                            <button wire:click="deselectAllContacts" class="px-3 h-6 bg-stone-200 hover:bg-stone-250 text-stone-700 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                Batal Pilih
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Contacts Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xxs font-bold text-stone-600">
                        <thead>
                            <tr class="border-b text-stone-450 uppercase text-[9px] tracking-wider">
                                <th class="py-2.5 w-8">
                                    @php
                                        $currentIds = json_encode($contacts->pluck('id')->toArray());
                                    @endphp
                                    <input type="checkbox" class="rounded border-stone-300 text-[#0A3D91] focus:ring-[#0A3D91]" 
                                        wire:click="selectedCustomerIds == [] ? selectAllContacts('{{ $currentIds }}') : deselectAllContacts()"
                                        {{ count($selectedCustomerIds) === count($contacts->pluck('id')->toArray()) && count($selectedCustomerIds) > 0 ? 'checked' : '' }} />
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
                                        <input type="checkbox" wire:model.live="selectedCustomerIds" value="{{ $contact->id }}" class="rounded border-stone-300 text-[#0A3D91] focus:ring-[#0A3D91]" />
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
                                        <button wire:click="openSingleModal({{ $contact->id }})" class="text-[9px] font-black uppercase text-[#0A3D91] hover:underline">
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

    <!-- Single Send Modal -->
    @if($isSingleModalOpen)
        <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-stone-150 shadow-2xl max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Kirim Pesan WhatsApp</h3>
                    <button wire:click="$set('isSingleModalOpen', false)" class="text-stone-400 hover:text-stone-700 text-sm font-bold">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-0.5">Penerima</label>
                        <div class="text-xs font-black text-stone-800">{{ $singleRecipientName }} ({{ $singleRecipientPhone }})</div>
                    </div>
                    
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Gunakan Template (Opsional)</label>
                        <select wire:model.live="singleTemplateName" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                            <option value="">-- Tulis Pesan Kustom --</option>
                            @foreach($templates as $temp)
                                <option value="{{ $temp->template_name }}">{{ $temp->template_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Isi Pesan</label>
                        <textarea wire:model="singleMessageText" rows="5" placeholder="Tulis isi pesan Anda di sini..." class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition"></textarea>
                        @error('singleMessageText') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button wire:click="sendSingleMessage" class="flex-1 h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Kirim Sekarang
                    </button>
                    <button wire:click="$set('isSingleModalOpen', false)" class="px-4 h-9 bg-stone-100 hover:bg-stone-150 text-stone-700 rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Send Modal -->
    @if($isBulkModalOpen)
        <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-stone-150 shadow-2xl max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider">Kirim Pesan Massal (Broadcast)</h3>
                    <button wire:click="$set('isBulkModalOpen', false)" class="text-stone-400 hover:text-stone-700 text-sm font-bold">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-0.5">Total Target Penerima</label>
                        <div class="text-xs font-black text-[#0A3D91]">{{ count($selectedCustomerIds) }} Kontak terpilih dari CRM</div>
                    </div>
                    
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Gunakan Template (Opsional)</label>
                        <select wire:model.live="bulkTemplateName" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                            <option value="">-- Tulis Pesan Kustom --</option>
                            @foreach($templates as $temp)
                                <option value="{{ $temp->template_name }}">{{ $temp->template_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Isi Pesan</label>
                        <textarea wire:model="bulkMessageText" rows="5" placeholder="Tulis pesan siaran massal..." class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition"></textarea>
                        <span class="text-[8px] text-stone-400 block mt-1">Gunakan tag @{{customer_name}} untuk menyisipkan nama pelanggan secara dinamis.</span>
                        @error('bulkMessageText') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button wire:click="sendBulkMessage" class="flex-1 h-9 bg-[#0A3D91] text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Mulai Broadcast
                    </button>
                    <button wire:click="$set('isBulkModalOpen', false)" class="px-4 h-9 bg-stone-100 hover:bg-stone-150 text-stone-700 rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
