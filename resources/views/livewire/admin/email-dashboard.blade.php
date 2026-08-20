<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-stone-200">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight uppercase">Email Communication Center</h1>
            <p class="text-xxs text-stone-500 font-bold uppercase tracking-wide mt-1">Kelola integrasi SMTP, template email, dan riwayat pesan email transaksional.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4 bg-white p-3.5 rounded-2xl border border-stone-150">
            <div class="flex items-center gap-2">
                <span class="text-xxs font-bold text-stone-600 uppercase">Aktifkan Saluran Email</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="emailEnabled" wire:change="toggleChannel" class="sr-only peer">
                    <div class="w-9 h-5 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0A3D91]"></div>
                </label>
            </div>
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
            <button wire:click="$set('activeTab', '{{ $tabKey }}')" class="pb-3 text-xxs font-black uppercase tracking-wide transition-all border-b-2 
                {{ $activeTab === $tabKey ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
                {{ $tabName }}
            </button>
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
        <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 max-w-xl">
            <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Konfigurasi Server SMTP</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Mail Host</label>
                    <input type="text" wire:model="host" placeholder="smtp.mailtrap.io" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Mail Port</label>
                    <input type="number" wire:model="port" placeholder="587" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Username</label>
                    <input type="text" wire:model="username" placeholder="user_smtp" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Password (Secret)</label>
                    <input type="password" wire:model="password" placeholder="••••••••" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Encryption</label>
                    <select wire:model="encryption" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">From Email Address</label>
                    <input type="email" wire:model="fromAddress" placeholder="no-reply@morehair.com" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">From Sender Name</label>
                    <input type="text" wire:model="fromName" placeholder="More Hair Studio" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                </div>
            </div>

            <div class="pt-2">
                <button wire:click="saveConfig" class="px-4 h-8 bg-stone-900 hover:bg-stone-850 text-white rounded-lg text-xxs font-bold uppercase tracking-wider transition">
                    Simpan Konfigurasi SMTP
                </button>
            </div>
        </div>

    @elseif($activeTab === 'templates')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Create Template -->
            <div class="bg-white p-5 rounded-2xl border border-stone-150 space-y-4 h-fit">
                <h3 class="text-xs font-black uppercase text-stone-900 tracking-wider pb-2 border-b">Tambah Template Email</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Nama Template</label>
                        <input type="text" wire:model="tempName" placeholder="email_booking_confirm" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        @error('tempName') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Subjek Email</label>
                        <input type="text" wire:model="tempSubject" placeholder="Konfirmasi Pemesanan - More Hair Studio" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        @error('tempSubject') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Body HTML</label>
                        <textarea wire:model="tempBody" rows="5" placeholder="<p>Halo @{{customer_name}}, reservasi anda terkonfirmasi.</p>" class="w-full text-xs p-3 rounded-xl border-stone-200 bg-stone-50/50 text-stone-750 focus:border-[#0A3D91] transition"></textarea>
                        @error('tempBody') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button wire:click="createTemplate" class="w-full h-9 bg-stone-900 hover:bg-stone-850 text-white rounded-xl text-xxs font-bold uppercase tracking-wider transition">
                        Buat Template Email
                    </button>
                </div>
            </div>

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
                            <button wire:click="deleteTemplate({{ $t->id }})" class="text-[9px] font-bold uppercase text-red-600 hover:underline">Hapus</button>
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
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xxs font-bold text-stone-600">
                    <thead>
                        <tr class="border-b text-stone-450 uppercase text-[9px] tracking-wider">
                            <th class="py-3">Waktu</th>
                            <th>Penerima Email</th>
                            <th>Subjek</th>
                            <th>Status</th>
                            <th>Error Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($logs as $log)
                            <tr>
                                <td class="py-3 text-stone-450 font-mono">{{ $log->created_at->format('d/m H:i') }}</td>
                                <td class="font-mono">{{ $log->recipient }}</td>
                                <td class="text-stone-850">{{ $log->subject }}</td>
                                <td>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider 
                                        @if($log->status === 'SENT') bg-emerald-50 text-emerald-800 border border-emerald-100
                                        @else bg-red-50 text-red-800 border border-red-100 @endif">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="text-[9px] font-normal text-stone-400 max-w-xs truncate">{{ $log->error_message ?? '-' }}</td>
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
