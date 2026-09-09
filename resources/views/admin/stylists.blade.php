@extends('layouts.admin')

@section('page_title')
    Stylists & Account Management
@endsection

@section('content')
<div class="space-y-6" x-data="{ importModalOpen: false, selectedFileName: '' }">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if(session()->has('error'))
        <x-ui.alert variant="danger">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    @if(session()->has('import_errors') && count(session('import_errors')) > 0)
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1.5 shadow-sm">
            <div class="font-extrabold flex items-center gap-1.5 text-amber-800">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Catatan Baris Yang Dilewati:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1 text-stone-700 font-mono text-[11px]">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($isCreating || $editingStylist)
        <!-- Create/Edit Form Card -->
        <x-ui.card subtitle="Profil & Kredensial Akun Login Stylist" title="{{ $editingStylist ? 'Edit Stylist & Akun: ' . $editingStylist->name : 'Tambah Stylist & Akun Baru' }}">
            <form method="POST" action="{{ $editingStylist ? route('admin.stylists.update', $editingStylist->id) : route('admin.stylists.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if($editingStylist)
                    @method('PUT')
                @endif

                <!-- Section 1: Photo & Basic Identity -->
                <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-4">
                    <div class="flex items-center space-x-2 border-b border-stone-200 pb-2.5">
                        <svg class="w-4 h-4 text-[#0A3D91]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h4 class="text-xs font-black text-stone-900 uppercase tracking-wider">Foto Profil & Identitas Stylist</h4>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pt-2">
                        <!-- Photo Preview Box -->
                        <div class="flex flex-col items-center space-y-2 flex-shrink-0">
                            <div class="relative w-28 h-28 rounded-2xl overflow-hidden border-2 border-[#0A3D91]/30 bg-white shadow-md group">
                                <img id="stylist-photo-preview" 
                                     src="{{ $editingStylist ? $editingStylist->display_photo : 'https://api.dicebear.com/7.x/avataaars/svg?seed=new-stylist' }}" 
                                     alt="Preview Foto" 
                                     class="w-full h-full object-cover">
                                <label for="stylist-photo-input" class="absolute inset-0 bg-black/40 text-white flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition duration-200 text-[10px] font-bold text-center px-2">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                                    Ubah Foto
                                </label>
                            </div>
                            <span class="text-[10px] text-stone-400 font-mono">JPG, PNG, WEBP (Max 3MB)</span>
                        </div>

                        <!-- Photo File Input & Helper -->
                        <div class="flex-grow space-y-3 w-full">
                            <div>
                                <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-1.5">Unggah File Foto Stylist</label>
                                <input type="file" 
                                       name="photo" 
                                       id="stylist-photo-input" 
                                       accept="image/png, image/jpeg, image/jpg, image/webp" 
                                       onchange="previewPhoto(event)"
                                       class="block w-full text-xs text-stone-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-[#0A3D91] file:text-white hover:file:bg-blue-800 file:cursor-pointer border border-stone-200 rounded-xl bg-white p-1">
                                <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                            </div>
                            <p class="text-[11px] text-stone-500 leading-relaxed">
                                Foto ini akan ditampilkan di kartu profil publik website, pemesanan online, dan dasbor tablet kiosk salon. Gunakan foto potret jernih dengan pencahayaan yang baik.
                            </p>
                        </div>
                    </div>

                    <!-- Name & Slug -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                        <div class="md:col-span-2">
                            <x-ui.input label="Nama Stylist" name="name" placeholder="Contoh: Raka Pratama" value="{{ old('name', $editingStylist ? $editingStylist->name : '') }}" required oninput="updateSlug(this.value)" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="Slug URL Publik" name="slug" id="stylist-slug-input" placeholder="contoh: raka-pratama" value="{{ old('slug', $editingStylist ? $editingStylist->slug : '') }}" required />
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Outlet, Specialization, Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-ui.select label="Studio / Outlet Penugasan" name="outlet_id" required>
                                <option value="">-- Pilih Outlet --</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ old('outlet_id', $editingStylist ? $editingStylist->outlet_id : '') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('outlet_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="Spesialisasi / Keahlian" name="specialization" placeholder="Contoh: Balayage & Coloring" value="{{ old('specialization', $editingStylist ? $editingStylist->specialization : '') }}" />
                            <x-input-error :messages="$errors->get('specialization')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="No. WhatsApp (Awali 62)" name="phone" placeholder="Contoh: 628123456789" value="{{ old('phone', $editingStylist ? $editingStylist->phone : '') }}" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.input label="Instagram (@username atau link)" name="instagram" placeholder="Contoh: @raka_hairart" value="{{ old('instagram', $editingStylist ? $editingStylist->instagram : '') }}" />
                            <x-input-error :messages="$errors->get('instagram')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="TikTok (@username atau link)" name="tiktok" placeholder="Contoh: @raka_styling" value="{{ old('tiktok', $editingStylist ? $editingStylist->tiktok : '') }}" />
                            <x-input-error :messages="$errors->get('tiktok')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Status & Bio -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-ui.select label="Status Stylist" name="status" required>
                                <option value="active" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'active' ? 'selected' : '' }}>Active (Aktif Melayani)</option>
                                <option value="inactive" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'inactive' ? 'selected' : '' }}>Inactive / Cuti</option>
                                <option value="pending_active" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'pending_active' ? 'selected' : '' }}>Pending Active</option>
                                <option value="pending_inactive" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'pending_inactive' ? 'selected' : '' }}>Pending Inactive</option>
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-1.5">Biografi Singkat (Bio)</label>
                            <textarea name="bio" rows="2" class="w-full px-4 py-2.5 bg-white border border-stone-200 text-stone-900 rounded-xl text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-400" placeholder="Kutipan keahlian, pengalaman, atau filosofi gaya rambut...">{{ old('bio', $editingStylist ? $editingStylist->bio : '') }}</textarea>
                            <x-input-error :messages="$errors->get('bio')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Unified Login Account Credentials -->
                <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-200/80 space-y-4">
                    <div class="flex items-center space-x-2 border-b border-blue-200/60 pb-2.5">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <h4 class="text-xs font-black text-blue-900 uppercase tracking-wider">Kredensial Akun Login Sistem (Role: Stylist)</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.input label="Email Akun Login" type="email" name="email" placeholder="contoh: raka@morehair.com" value="{{ old('email', $editingStylist && $editingStylist->user ? $editingStylist->user->email : '') }}" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            <span class="block mt-1 text-[10px] text-stone-400">Digunakan oleh stylist untuk login ke Dasbor Stylist.</span>
                        </div>
                        <div>
                            <x-ui.input label="Password Akun" type="password" name="password" placeholder="{{ $editingStylist ? 'Kosongkan jika tidak diubah' : 'Default: password123 jika dikosongkan' }}" />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                            <span class="block mt-1 text-[10px] text-stone-400">
                                {{ $editingStylist ? 'Isi hanya jika ingin mereset/mengganti password akun stylist.' : 'Minimal 6 karakter. Default: password123' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('admin.stylists') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Batal</a>
                    <x-ui.button variant="primary" type="submit">
                        {{ $editingStylist ? 'Simpan Perubahan Stylist & Akun' : 'Tambah Stylist & Buat Akun' }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @else
        <!-- List View Card -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4 pb-1">
                <form method="GET" action="{{ route('admin.stylists') }}" class="w-full lg:max-w-xs">
                    <x-ui.input placeholder="Cari nama, nomor WhatsApp, email..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                </form>
                
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Download Template Excel -->
                    <a href="{{ route('admin.stylists.template') }}" title="Unduh file template Excel (.xlsx) untuk pengisian massal data stylist" class="inline-flex items-center justify-center px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300/80 font-bold rounded-xl text-xs transition shadow-xs space-x-1.5 group">
                        <svg class="w-4 h-4 text-emerald-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download Template</span>
                    </a>

                    <!-- Export Data Excel -->
                    <a href="{{ route('admin.stylists.export') }}" title="Unduh seluruh data stylist yang ada ke dalam file Excel (.xlsx)" class="inline-flex items-center justify-center px-3.5 py-2.5 bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 font-bold rounded-xl text-xs transition shadow-xs space-x-1.5">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export Data</span>
                    </a>

                    <!-- Import Excel / CSV -->
                    <button type="button" @click="importModalOpen = true" title="Unggah file Excel atau CSV untuk mendaftarkan stylist secara massal" class="inline-flex items-center justify-center px-3.5 py-2.5 bg-blue-50 hover:bg-blue-100 text-[#0A3D91] border border-blue-200 font-bold rounded-xl text-xs transition shadow-xs space-x-1.5">
                        <svg class="w-4 h-4 text-[#0A3D91]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span>Import Excel / CSV</span>
                    </button>

                    <!-- Add Single Stylist -->
                    <a href="?create=1" class="inline-flex items-center justify-center px-4 py-2.5 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-xs space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add New Stylist & Account</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-stone-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">Stylist & Profile</th>
                            <th class="py-3.5 px-4">Akun Login (Email)</th>
                            <th class="py-3.5 px-4">Outlet Studio</th>
                            <th class="py-3.5 px-4">Spesialisasi</th>
                            <th class="py-3.5 px-4">WhatsApp</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($stylists as $stylist)
                            <tr class="hover:bg-stone-50/70 transition text-stone-700">
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $stylist->display_photo }}" alt="{{ $stylist->name }}" class="w-10 h-10 rounded-xl object-cover border border-stone-200 shadow-sm flex-shrink-0">
                                        <div>
                                            <div class="font-extrabold text-stone-900 text-sm flex items-center gap-1.5">
                                                <span>{{ $stylist->name }}</span>
                                                <a href="{{ url('/' . $stylist->slug) }}" target="_blank" title="Lihat Profil Publik" class="text-stone-400 hover:text-blue-600 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            </div>
                                            <span class="text-[11px] font-mono text-stone-400 font-medium">/{{ $stylist->slug }}</span>
                                            @if($stylist->instagram || $stylist->tiktok)
                                                <div class="flex items-center gap-2 mt-0.5 text-[10px] text-stone-500">
                                                    @if($stylist->instagram)
                                                        <span>IG: <strong>{{ '@' . ltrim($stylist->instagram, '@') }}</strong></span>
                                                    @endif
                                                    @if($stylist->tiktok)
                                                        <span>TT: <strong>{{ '@' . ltrim($stylist->tiktok, '@') }}</strong></span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($stylist->user)
                                        <span class="font-mono text-stone-800 bg-stone-100 px-2 py-1 rounded text-xxs font-semibold border border-stone-200">
                                            {{ $stylist->user->email }}
                                        </span>
                                    @else
                                        <span class="text-rose-500 text-xxs font-bold uppercase tracking-wider bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                            Belum Ada Akun
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-stone-600 font-medium">{{ $stylist->outlet ? $stylist->outlet->name : '-' }}</td>
                                <td class="py-3 px-4 font-medium">{{ $stylist->specialization ?? '-' }}</td>
                                <td class="py-3 px-4 font-mono text-stone-600">{{ $stylist->phone ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-extrabold uppercase tracking-wider {{ $stylist->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-600 border border-stone-200' }}">
                                        {{ $stylist->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center space-x-2">
                                        <a href="?edit={{ $stylist->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition shadow-sm">
                                            Edit
                                        </a>
                                        @if($stylist->user_id)
                                            <a href="{{ route('impersonate.start', ['id' => $stylist->user_id]) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-lg text-[10px] uppercase tracking-wider shadow-sm transition">
                                                Masuk Akun
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.stylists.delete', $stylist->id) }}" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun login dan menonaktifkan profil stylist {{ addslashes($stylist->name) }}? Akun login user akan dihapus dan jadwal kerja akan dinonaktifkan secara permanen.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition shadow-sm" title="Hapus Akun & Profil Stylist">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-stone-400">Belum ada data stylist.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $stylists->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Dialog: Import Excel / CSV -->
    <div x-show="importModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" 
         style="display: none;">
        
        <!-- Backdrop -->
        <div x-show="importModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="importModalOpen = false" 
             class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"></div>

        <!-- Modal Box -->
        <div x-show="importModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="relative bg-white rounded-3xl shadow-2xl border border-stone-200 w-full max-w-lg overflow-hidden z-10">
            
            <!-- Header -->
            <div class="px-6 py-5 border-b border-stone-100 bg-stone-50/70 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-[#0A3D91] shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-stone-900">Import Stylist via Excel / CSV</h3>
                        <p class="text-xxs text-stone-500">Daftarkan banyak hair stylist & akun login sekaligus</p>
                    </div>
                </div>
                <button type="button" @click="importModalOpen = false" class="text-stone-400 hover:text-stone-600 p-1.5 rounded-xl hover:bg-stone-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.stylists.import') }}" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf

                <!-- Download Template Banner inside modal -->
                <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-stone-800">Belum punya format template?</span>
                        <p class="text-xxs text-stone-500 leading-normal">Unduh template resmi dengan panduan kolom & contoh data.</p>
                    </div>
                    <a href="{{ route('admin.stylists.template') }}" class="flex-shrink-0 inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-300 font-bold rounded-xl text-xxs transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Unduh Excel</span>
                    </a>
                </div>

                <!-- Dropzone File Selector -->
                <div>
                    <label class="block text-[10px] uppercase tracking-widest text-stone-500 font-extrabold mb-1.5">Pilih File Excel (.xlsx, .xls) atau CSV *</label>
                    <div class="relative border-2 border-dashed border-stone-300 hover:border-[#0A3D91] rounded-2xl p-6 text-center bg-stone-50/50 hover:bg-blue-50/30 transition group cursor-pointer">
                        <input type="file" 
                               name="file" 
                               id="excel-file-input" 
                               accept=".xlsx, .xls, .csv, text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" 
                               required
                               @change="selectedFileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        
                        <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-stone-200 flex items-center justify-center text-stone-400 group-hover:text-[#0A3D91] group-hover:border-blue-200 group-hover:scale-105 transition shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <template x-if="!selectedFileName">
                                <div>
                                    <span class="text-xs font-bold text-stone-800 block">Pilih file atau seret file ke sini</span>
                                    <span class="text-xxs text-stone-400">Format yang didukung: .xlsx, .xls, .csv (Maks. 10MB)</span>
                                </div>
                            </template>
                            <template x-if="selectedFileName">
                                <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold font-mono">
                                    <span x-text="selectedFileName"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Fallback Outlet Selection -->
                <div>
                    <label class="block text-[10px] uppercase tracking-widest text-stone-500 font-extrabold mb-1.5">Outlet Studio Default</label>
                    <select name="default_outlet_id" class="w-full px-4 py-2.5 bg-white border border-stone-200 text-stone-900 rounded-xl text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91]">
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                    <span class="block mt-1 text-xxs text-stone-400">Digunakan apabila kolom Outlet Studio pada file Excel dikosongkan.</span>
                </div>

                <!-- System Info Points -->
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200/70 space-y-1.5 text-xxs text-stone-600">
                    <div class="font-extrabold text-[#0A3D91] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Sistem Otomatis:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-1">
                        <li>Akun login (role Stylist) otomatis dibuatkan untuk setiap baris.</li>
                        <li>Password default akun: <strong>password123</strong> (jika di file kosong).</li>
                        <li>Jadwal kerja aktif 7 hari (10:00 - 20:00) otomatis diaktifkan.</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex justify-end items-center space-x-3 pt-3 border-t border-stone-100">
                    <button type="button" @click="importModalOpen = false" class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold rounded-xl text-xs transition border border-stone-200">
                        Batal
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0A3D91] hover:bg-blue-800 text-white font-extrabold rounded-xl text-xs transition shadow-sm space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Mulai Import Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateSlug(val) {
        @if(!$editingStylist)
            const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            document.getElementById('stylist-slug-input').value = slug;
        @endif
    }

    function previewPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('stylist-photo-preview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
