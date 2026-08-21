@extends('layouts.admin')

@section('page_title')
    Outlet Admin Panel - {{ auth()->user()->outlet ? auth()->user()->outlet->name : 'Studio' }}
@endsection

@section('content')
<div>
    <!-- Scoped Hero Banner -->
    <div class="glass-panel p-8 rounded-3xl mb-10 flex flex-col md:flex-row justify-between items-start md:items-center bg-white border border-stone-200 relative overflow-hidden">
        <div class="space-y-2">
            <h2 class="text-2xl font-bold tracking-wide text-stone-900">{{ auth()->user()->outlet ? auth()->user()->outlet->name : 'Studio' }} Panel</h2>
            <p class="text-xs text-stone-500 max-w-xl leading-relaxed">
                Kelola pesanan booking customer, alokasi stylist aktif, kehadiran tim, dan monitor grafik pengerjaan real-time di studio Anda.
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <a href="{{ route('tablet.styscreen') }}" target="_blank" class="flex items-center justify-center px-5 py-2.5 bg-[#0A3D91] text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-blue-800 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Buka Styscreen Kasir
            </a>
            <div class="text-xxs font-mono uppercase tracking-widest text-[#0A3D91] font-extrabold bg-blue-50 border border-blue-100 px-4 py-2 rounded-xl text-center">
                <span>Outlet Scoped Access</span>
            </div>
        </div>
    </div>

    <!-- Scoped Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        <!-- Revenue Card -->
        <x-ui.card subtitle="Outlet Revenue" title="Rp {{ number_format($totalRevenue, 0, ',', '.') }}">
            <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-green-600 font-extrabold uppercase tracking-wide">
                <span>Sum of completed net payments at this studio</span>
            </div>
        </x-ui.card>

        <!-- Bookings Card -->
        <x-ui.card subtitle="Outlet Bookings" title="{{ $totalBookings }}">
            <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-stone-450 font-extrabold uppercase tracking-wide">
                <span>Total online and walk-in appointments</span>
            </div>
        </x-ui.card>
    </div>

    @if(session()->has('message'))
        <div class="mb-6">
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-6">
            <x-ui.alert variant="danger">
                {{ session('error') }}
            </x-ui.alert>
        </div>
    @endif

    <form method="POST" action="{{ route('outlet.settings.save') }}" enctype="multipart/form-data" class="space-y-8 mb-10">
        @csrf
        <input type="hidden" name="removed_gallery_indices" id="removed-gallery-indices" value="[]">

        <!-- Configuration Panel 1: Attendance Configuration -->
        <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200 shadow-sm space-y-6">
            <div>
                <h3 class="text-lg font-bold text-stone-900 mb-1">Pengaturan Absensi Karyawan / Hairstylist (Tablet Kiosk)</h3>
                <p class="text-xs text-stone-500">Kelola batas rentang waktu absensi masuk (pagi) dan absensi pulang (sore) untuk karyawan di tablet.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <x-ui.input label="Absen Masuk Mulai" type="time" name="attendanceStart" value="{{ old('attendanceStart', $attendanceStart) }}" required />
                    <x-input-error :messages="$errors->get('attendanceStart')" class="mt-1" />
                </div>
                <div>
                    <x-ui.input label="Absen Masuk Batas Akhir" type="time" name="attendanceEnd" value="{{ old('attendanceEnd', $attendanceEnd) }}" required />
                    <x-input-error :messages="$errors->get('attendanceEnd')" class="mt-1" />
                </div>
                <div>
                    <x-ui.input label="Absen Pulang Mulai" type="time" name="clockOutStart" value="{{ old('clockOutStart', $clockOutStart) }}" required />
                    <x-input-error :messages="$errors->get('clockOutStart')" class="mt-1" />
                </div>
                <div>
                    <x-ui.input label="Absen Pulang Batas Akhir" type="time" name="clockOutEnd" value="{{ old('clockOutEnd', $clockOutEnd) }}" required />
                    <x-input-error :messages="$errors->get('clockOutEnd')" class="mt-1" />
                </div>
            </div>
        </div>

        <!-- Configuration Panel 2: Booking and Tolerance Settings -->
        <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200 shadow-sm space-y-6">
            <div>
                <h3 class="text-lg font-bold text-stone-900 mb-1">Pengaturan Toleransi & Pemesanan Online</h3>
                <p class="text-xs text-stone-500">Kelola batas minimum waktu pemesanan online sebelum sesi dimulai dan toleransi keterlambatan hadir.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                <div>
                    <x-ui.input label="Batas Minimum Booking Online (Jam)" type="number" min="0" max="72" name="bookingLeadTime" value="{{ old('bookingLeadTime', $bookingLeadTime) }}" required />
                    <x-input-error :messages="$errors->get('bookingLeadTime')" class="mt-1" />
                </div>
                <div>
                    <x-ui.select label="Auto-Cancel Grace Period" name="checkinGraceActive" id="grace-period-select" onchange="toggleGraceInput(this.value)">
                        <option value="1" {{ old('checkinGraceActive', $checkinGraceActive) == 1 ? 'selected' : '' }}>Aktif (Auto-Cancel)</option>
                        <option value="0" {{ old('checkinGraceActive', $checkinGraceActive) == 0 ? 'selected' : '' }}>Nonaktif</option>
                    </x-ui.select>
                    <x-input-error :messages="$errors->get('checkinGraceActive')" class="mt-1" />
                </div>
                <div>
                    <x-ui.input label="Batas Waktu Hadir (Menit)" type="number" min="1" max="180" name="checkinGraceMinutes" id="grace-minutes-input" value="{{ old('checkinGraceMinutes', $checkinGraceMinutes) }}" />
                    <x-input-error :messages="$errors->get('checkinGraceMinutes')" class="mt-1" />
                </div>
            </div>
        </div>

        <!-- Configuration Panel 3: Outlet Profile Details -->
        <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200 shadow-sm space-y-6">
            <div>
                <h3 class="text-lg font-bold text-stone-900 mb-1">Pengaturan Profil Khusus Outlet</h3>
                <p class="text-xs text-stone-500">Kelola deskripsi profil, tautan Google Maps iframe, dan tautan foto galeri untuk halaman profil publik studio Anda.</p>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-stone-750 uppercase tracking-wide mb-2">Tentang Kami / Deskripsi</label>
                    <textarea 
                        name="description" 
                        rows="4" 
                        class="w-full p-4 border border-stone-200 rounded-xl text-xs focus:ring-[#0A3D91] focus:border-[#0A3D91] outline-none"
                        placeholder="Tulis cerita menarik tentang studio Anda..."
                    >{{ old('description', $description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-stone-750 uppercase tracking-wide mb-2">Google Maps Embed Iframe Code</label>
                        <textarea 
                            name="mapIframe" 
                            rows="4" 
                            class="w-full p-4 border border-stone-200 rounded-xl text-xs font-mono focus:ring-[#0A3D91] focus:border-[#0A3D91] outline-none"
                            placeholder='e.g. &lt;iframe src="https://www.google.com/maps/embed?..." ...&gt;&lt;/iframe&gt;'
                        >{{ old('mapIframe', $mapIframe) }}</textarea>
                        <x-input-error :messages="$errors->get('mapIframe')" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-750 uppercase tracking-wide mb-2">Unggah Foto Galeri (Bisa Banyak Foto)</label>
                        <div class="space-y-3">
                            <!-- Existing Gallery Grid -->
                            @if(count($gallery) > 0)
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 border p-3.5 rounded-xl bg-stone-50/50 max-h-40 overflow-y-auto">
                                    @foreach($gallery as $idx => $photoUrl)
                                        <div id="gallery-photo-{{ $idx }}" class="relative group aspect-square rounded-lg overflow-hidden border bg-white">
                                            <img src="{{ $photoUrl }}" class="w-full h-full object-cover" />
                                            <button type="button" onclick="removePhoto({{ $idx }})" class="absolute inset-0 bg-black/45 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition text-[10px] font-bold uppercase tracking-wider">
                                                Hapus
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- File Upload Input Dropzone -->
                            <div class="relative border-2 border-dashed border-stone-250 hover:border-stone-400 p-4 rounded-xl text-center bg-stone-50/10 cursor-pointer transition">
                                <input type="file" name="newPhotos[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                <div class="space-y-1.5 pointer-events-none">
                                    <svg class="w-6 h-6 mx-auto text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <p class="text-xxs font-bold text-stone-600">Klik atau geser foto ke sini untuk mengunggah</p>
                                    <p class="text-[9px] text-stone-400">Mendukung format JPG, PNG, WEBP (Maks 5MB per file)</p>
                                </div>
                            </div>
                            
                            <x-input-error :messages="$errors->get('newPhotos')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Panel 4: Active Services Checklist -->
        <div class="glass-panel p-8 rounded-3xl bg-white border border-stone-200 shadow-sm space-y-6">
            <div>
                <h3 class="text-lg font-bold text-stone-900 mb-1">Layanan Aktif & Tarif Custom</h3>
                <p class="text-xs text-stone-500">Pilih layanan apa saja yang ditawarkan di studio ini. Anda juga dapat menentukan harga dan durasi khusus yang berbeda dari tarif default pusat.</p>
            </div>
            
            <div class="overflow-x-auto rounded-xl border border-stone-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 text-stone-400 font-extrabold uppercase tracking-widest text-[9px] border-b border-stone-250 font-mono">
                            <th class="p-4 w-16 text-center">Tampilkan</th>
                            <th class="p-4">Nama Layanan</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Tarif Default</th>
                            <th class="p-4">Tarif Custom Outlet (Rp)</th>
                            <th class="p-4">Durasi Default</th>
                            <th class="p-4">Durasi Custom (Menit)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-150 text-stone-700">
                        @foreach($services as $s)
                            <tr class="hover:bg-stone-50/50 transition">
                                <td class="p-4 text-center">
                                    <input 
                                        type="checkbox" 
                                        name="selectedServices[{{ $s->id }}]" 
                                        value="1"
                                        id="selected-services-{{ $s->id }}"
                                        onchange="toggleOverrideInputs({{ $s->id }}, this.checked)"
                                        {{ old('selectedServices.'.$s->id, $selectedServices[$s->id]) ? 'checked' : '' }}
                                        class="w-4 h-4 text-[#0A3D91] border-stone-300 rounded focus:ring-[#0A3D91]"
                                    />
                                </td>
                                <td class="p-4 font-bold text-stone-900 uppercase tracking-tight text-[11px] font-sans">{{ $s->name }}</td>
                                <td class="p-4 text-xxs uppercase tracking-wider text-stone-400 font-extrabold font-mono">{{ $s->category->name }}</td>
                                <td class="p-4 font-mono text-stone-550">Rp {{ number_format($s->price, 0, ',', '.') }}</td>
                                <td class="p-4">
                                    <input 
                                        type="number" 
                                        name="customPrices[{{ $s->id }}]" 
                                        id="custom-price-{{ $s->id }}"
                                        value="{{ old('customPrices.'.$s->id, $customPrices[$s->id]) }}"
                                        placeholder="{{ (int)$s->price }}"
                                        class="w-32 px-3 py-1.5 border border-stone-200 rounded-lg text-xs font-mono focus:ring-[#0A3D91] focus:border-[#0A3D91] outline-none disabled:bg-stone-50 disabled:text-stone-400"
                                    />
                                </td>
                                <td class="p-4 text-stone-550 font-sans">{{ $s->duration }} Menit</td>
                                <td class="p-4 font-sans">
                                    <input 
                                        type="number" 
                                        name="customDurations[{{ $s->id }}]" 
                                        id="custom-duration-{{ $s->id }}"
                                        value="{{ old('customDurations.'.$s->id, $customDurations[$s->id]) }}"
                                        placeholder="{{ $s->duration }}"
                                        class="w-24 px-3 py-1.5 border border-stone-200 rounded-lg text-xs focus:ring-[#0A3D91] focus:border-[#0A3D91] outline-none disabled:bg-stone-50 disabled:text-stone-400"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <x-ui.button variant="primary" type="submit" class="px-8 h-[48px] rounded-lg shadow-sm font-bold uppercase tracking-wide">
                Simpan Semua Pengaturan
            </x-ui.button>
        </div>
    </form>

    <!-- Scoped Recent Bookings Table -->
    <div class="glass-panel p-8 rounded-3xl border border-stone-200 bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-stone-900">Studio Bookings</h3>
            <span class="text-xxs uppercase tracking-widest font-mono text-stone-400">Live studio activity feed</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-stone-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="py-4 px-5">Code</th>
                        <th class="py-4 px-5">Customer</th>
                        <th class="py-4 px-5">Stylist</th>
                        <th class="py-4 px-5">Date / Time</th>
                        <th class="py-4 px-5 text-right">Price</th>
                        <th class="py-4 px-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @forelse($recentBookings as $booking)
                        <tr class="hover:bg-stone-50/70 transition duration-150 text-stone-700">
                            <td class="py-4 px-5 font-mono text-[#0A3D91] font-bold tracking-wide">{{ $booking->booking_code }}</td>
                            <td class="py-4 px-5 font-bold text-stone-800">{{ $booking->customer->name }}</td>
                            <td class="py-4 px-5 text-stone-600 font-medium">{{ $booking->stylist->name }}</td>
                            <td class="py-4 px-5 text-stone-550">{{ $booking->booking_date->format('d M Y') }}</td>
                            <td class="py-4 px-5 text-right font-mono font-bold text-stone-900">Rp {{ number_format($booking->net_amount, 0, ',', '.') }}</td>
                            <td class="py-4 px-5 text-center">
                                <x-ui.badge variant="{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'primary') }}">
                                    {{ $booking->status }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-stone-400">Belum ada booking terekam untuk outlet ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let removedIndices = [];
    function removePhoto(idx) {
        removedIndices.push(idx);
        document.getElementById('removed-gallery-indices').value = JSON.stringify(removedIndices);
        document.getElementById('gallery-photo-' + idx).remove();
    }

    function toggleGraceInput(val) {
        document.getElementById('grace-minutes-input').disabled = (val == 0);
    }

    function toggleOverrideInputs(id, enabled) {
        document.getElementById('custom-price-' + id).disabled = !enabled;
        document.getElementById('custom-duration-' + id).disabled = !enabled;
    }

    // Init state checks
    window.addEventListener('DOMContentLoaded', () => {
        toggleGraceInput(document.getElementById('grace-period-select').value);
        @foreach($services as $s)
            toggleOverrideInputs({{ $s->id }}, document.getElementById('selected-services-{{ $s->id }}').checked);
        @endforeach
    });
</script>
@endsection
