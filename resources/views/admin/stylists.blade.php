@extends('layouts.admin')

@section('page_title')
    Stylists & Account Management
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if(session()->has('error'))
        <x-ui.alert variant="error">
            {{ session('error') }}
        </x-ui.alert>
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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('admin.stylists') }}" class="w-full md:max-w-xs">
                    <x-ui.input placeholder="Cari nama, nomor WhatsApp, email..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                </form>
                <a href="?create=1" class="inline-flex items-center justify-center px-4 py-2.5 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Stylist & Account</span>
                </a>
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
