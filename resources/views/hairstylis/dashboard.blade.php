@extends('layouts.admin')

@section('page_title')
    Dasbor Hairstylist
@endsection

@section('content')

    @if ($error_unlinked)
        <div class="p-6 max-w-lg mx-auto bg-white border border-stone-200 rounded-2xl shadow-sm text-center space-y-4">
            <div class="text-4xl">⚠️</div>
            <h3 class="font-extrabold text-stone-900 text-lg uppercase tracking-tight">Akun Belum Terhubung</h3>
            <p class="text-stone-500 text-sm">Akun user Anda belum terhubung dengan data Stylist di Outlet manapun. Silakan hubungi Administrator atau Outlet Manager Anda untuk mengaitkan akun ini.</p>
        </div>
    @else
        <div class="space-y-8">
            <!-- Alert Notifications -->
            @if(session()->has('message'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <span>✅</span>
                        <span>{{ session('message') }}</span>
                    </div>
                </div>
            @endif

            @if(session()->has('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <span>❌</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Dashboard Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Sidebar: Member Profile Card & Attendance QR -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Premium Stylist ID Card -->
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0d1b3e] to-[#0A3D91] text-white p-6 shadow-xl border border-blue-900/30 flex flex-col justify-between h-[230px] group hover:shadow-2xl hover:shadow-brand-500/10 transition-all duration-300">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-2xl group-hover:bg-white/10 transition duration-500"></div>
                        <div class="absolute right-4 bottom-4 opacity-10">
                            <div class="w-16 h-16 bg-white grid grid-cols-4 gap-0.5 p-1 rounded">
                                <div class="bg-black"></div><div class="bg-black"></div><div class="bg-white"></div><div class="bg-black"></div>
                                <div class="bg-white"></div><div class="bg-black"></div><div class="bg-black"></div><div class="bg-white"></div>
                                <div class="bg-black"></div><div class="bg-white"></div><div class="bg-black"></div><div class="bg-black"></div>
                                <div class="bg-black"></div><div class="bg-black"></div><div class="bg-white"></div><div class="bg-black"></div>
                            </div>
                        </div>

                        <!-- Top row -->
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[9px] uppercase tracking-widest font-extrabold text-blue-200">Official Stylist ID</span>
                                <h3 class="text-lg font-extrabold tracking-tight mt-0.5 uppercase">{{ $stylist->name }}</h3>
                                <p class="text-[10px] text-blue-200 uppercase font-semibold tracking-wider mt-0.5">{{ $stylist->specialization ?: 'Stylist' }}</p>
                            </div>
                            <div class="px-2.5 py-1 bg-white/10 rounded-lg backdrop-blur-md border border-white/10 text-xs font-bold text-amber-300 flex items-center space-x-1 shadow-sm">
                                <span>★</span>
                                <span>{{ number_format($stylist->rating, 1) }}</span>
                            </div>
                        </div>

                        <!-- Bottom row -->
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[9px] uppercase tracking-wider text-blue-300 font-medium">Outlet</p>
                                <p class="text-xs font-bold">{{ $stylist->outlet->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] uppercase tracking-wider text-blue-300 font-medium font-mono">Profile ID</p>
                                <p class="text-xs font-mono font-bold text-blue-100">MH-ST-{{ str_pad($stylist->id, 3, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Card (Stylist Absensi Scanner) -->
                    <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm flex flex-col items-center text-center space-y-4">
                        <span class="text-[10px] text-brand-600 uppercase tracking-widest font-extrabold">QR Code Absensi Saya</span>
                        <div class="p-3 bg-white border border-stone-200 rounded-2xl shadow-inner">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=stylist:{{ $stylist->id }}" alt="QR Code Absen" class="h-40 w-40">
                        </div>
                        <p class="text-stone-500 text-[10px] leading-relaxed px-4">
                            Tunjukkan QR Code ini pada kamera Scanner tablet di outlet untuk melakukan absensi (Clock In / Clock Out).
                        </p>
                        <a href="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=stylist:{{ $stylist->id }}" target="_blank" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200 shadow-sm">
                            Simpan QR Code
                        </a>
                    </div>

                    <!-- Leave and Activation Status Control -->
                    <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                        <h4 class="font-extrabold text-stone-900 text-xs uppercase tracking-wider border-b border-stone-100 pb-3">Status Kehadiran & Cuti</h4>
                        
                        <div class="flex items-center justify-between py-2">
                            <span class="text-xs text-stone-500">Status Akun Saat Ini:</span>
                            @if ($stylist->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">Aktif</span>
                            @elseif ($stylist->status === 'inactive')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-extrabold bg-stone-100 text-stone-600 border border-stone-200 uppercase tracking-wider">Cuti / Nonaktif</span>
                            @elseif ($stylist->status === 'pending_inactive' || $stylist->status === 'pending_leave')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">Pending Cuti</span>
                            @elseif ($stylist->status === 'pending_active')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">Pending Aktif</span>
                            @endif
                        </div>

                        <p class="text-xxs text-stone-400 leading-relaxed italic">
                            *Jika status Anda "Cuti" atau "Pending", nama Anda tidak akan muncul pada daftar pemesanan pelanggan untuk menghindari salah jadwal.
                        </p>

                        <div class="pt-2">
                            @if ($stylist->status === 'active')
                                <form method="POST" action="{{ route('stylist.leave.request') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-center justify-center inline-flex items-center px-4 py-2.5 bg-rose-50 border border-rose-200 rounded-xl font-bold text-xs text-rose-700 hover:bg-rose-100 transition shadow-sm">
                                        Ajukan Cuti / Nonaktif
                                    </button>
                                </form>
                            @elseif ($stylist->status === 'inactive')
                                <form method="POST" action="{{ route('stylist.activate.request') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-center justify-center inline-flex items-center px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl font-bold text-xs text-emerald-700 hover:bg-emerald-100 transition shadow-sm">
                                        Ajukan Aktif Kembali
                                    </button>
                                </form>
                            @else
                                <button disabled class="w-full text-center justify-center inline-flex items-center px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl font-bold text-xs text-stone-400 cursor-not-allowed shadow-inner">
                                    Menunggu Persetujuan Admin...
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Dashboard Main: Schedules & Performance & profile Form -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Haircut Schedules with Calendar -->
                    <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-6">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center border-b border-stone-100 pb-4 gap-4">
                            <div>
                                <h4 class="font-extrabold text-stone-900 text-sm uppercase tracking-wider">Jadwal Potong Rambut</h4>
                                <p class="text-stone-400 text-[10px] mt-0.5">Daftar booking pelanggan teralokasi ke Anda.</p>
                            </div>
                            <div class="flex items-center space-x-2 w-full sm:max-w-xs">
                                <span class="text-xxs text-stone-400 font-bold uppercase whitespace-nowrap">Pilih Tanggal:</span>
                                <input type="date" name="date" value="{{ $searchDate }}" onchange="window.location.href='{{ route('stylist.dashboard') }}?date=' + this.value" class="w-full text-xs border-stone-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-sm px-3.5 py-2">
                            </div>
                        </div>

                        <!-- Premium Horizontal Weekly Calendar Selector -->
                        <div class="bg-stone-50/50 p-4 rounded-2xl border border-stone-100">
                            <div class="flex items-center justify-between overflow-x-auto space-x-3 pb-2 scrollbar-none scroll-smooth">
                                @foreach($weekDays as $day)
                                    <a href="{{ route('stylist.dashboard', ['date' => $day['date']]) }}" 
                                       class="flex-1 min-w-[55px] p-2.5 rounded-xl border flex flex-col items-center justify-between transition-all duration-350 focus:outline-none 
                                       {{ $day['isActive'] 
                                           ? 'bg-gradient-to-br from-[#0d1b3e] to-[#0A3D91] border-transparent text-white shadow-md scale-105' 
                                           : 'bg-white border-stone-200 text-stone-700 hover:border-[#0A3D91] hover:bg-stone-50' }}"
                                    >
                                        <span class="text-[9px] uppercase tracking-wider font-extrabold {{ $day['isActive'] ? 'text-blue-200' : 'text-stone-400' }}">
                                            {{ $day['dayName'] }}
                                        </span>
                                        <span class="text-sm font-black mt-1 leading-none">
                                            {{ $day['dayNum'] }}
                                        </span>
                                        @if($day['isToday'])
                                            <span class="w-1 h-1 rounded-full mt-1.5 {{ $day['isActive'] ? 'bg-amber-300' : 'bg-brand-500' }}"></span>
                                        @else
                                            <span class="w-1 h-1 rounded-full mt-1.5 opacity-0"></span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Schedules list -->
                        <div class="space-y-4">
                            @forelse ($schedules as $booking)
                                <div class="border border-stone-200/80 rounded-2xl p-4 bg-stone-50/50 hover:bg-stone-50 hover:border-brand-500/30 transition duration-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="p-3 bg-brand-50 rounded-xl text-[#0A3D91] font-bold text-xs text-center min-w-[70px] border border-brand-100 font-mono shadow-sm">
                                            <span class="block text-[10px] text-[#0A3D91] font-medium">Slot</span>
                                            {{ $booking->items->first()?->start_time ? substr($booking->items->first()->start_time, 0, 5) : '00:00' }}
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h5 class="font-bold text-stone-950 text-sm tracking-tight">{{ $booking->customer->name }}</h5>
                                                <span class="text-[9px] font-mono bg-stone-200/80 text-stone-600 px-1.5 py-0.5 rounded font-bold uppercase">{{ $booking->booking_code }}</span>
                                            </div>
                                            <p class="text-stone-500 text-xs font-semibold mt-0.5">{{ $booking->items->first()?->service->name ?? 'Service Haircut' }}</p>
                                            
                                            @if($booking->notes)
                                                <p class="text-xxs text-stone-400 mt-1.5 italic font-medium">Catatan: "{{ $booking->notes }}"</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between md:justify-end space-x-4 pt-2 md:pt-0 border-t md:border-t-0 border-stone-200/80">
                                        <div class="flex items-center space-x-2 w-full justify-between md:justify-end">
                                            <!-- WhatsApp chat link -->
                                            <a href="https://wa.me/{{ $booking->customer->phone }}" target="_blank" class="p-2.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-xl text-emerald-600 transition shadow-sm" title="Chat via WhatsApp">
                                                <svg class="h-4.5 w-4.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.114-2.904-6.989C16.558 1.878 14.077.85 11.448.85c-5.44 0-9.866 4.42-9.87 9.866-.001 1.77.461 3.5 1.337 5.016l-1.01 3.687 3.752-.983zm13.11-6.173c-.29-.146-1.72-.85-1.986-.948-.266-.097-.461-.146-.656.146-.195.29-.757.948-.928 1.14-.17.195-.341.219-.63.073-.29-.147-1.228-.452-2.338-1.442-.864-.77-1.447-1.72-1.617-2.011-.17-.29-.018-.447.127-.592.13-.13.29-.34.436-.51.145-.17.195-.29.29-.485.097-.194.048-.364-.025-.51-.072-.146-.656-1.579-.9-2.162-.236-.572-.477-.495-.656-.504-.17-.008-.364-.01-.559-.01-.195 0-.51.073-.777.364-.266.29-1.02 1.02-1.02 2.48s1.07 2.87 1.218 3.064c.145.195 2.1 3.207 5.09 4.498.71.307 1.265.49 1.696.627.712.227 1.36.195 1.871.118.571-.085 1.72-.704 1.962-1.385.243-.68.243-1.261.17-1.385-.072-.124-.266-.194-.559-.34z"/>
                                                </svg>
                                            </a>

                                            <!-- Status operations -->
                                            @if ($booking->status === 'pending')
                                                <form method="POST" action="{{ route('stylist.booking.confirm', $booking->id) }}">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-600 font-extrabold text-[10px] uppercase rounded-xl tracking-wider transition border border-brand-100 shadow-sm">
                                                        Konfirmasi
                                                    </button>
                                                </form>
                                            @elseif ($booking->status === 'confirmed')
                                                <form method="POST" action="{{ route('stylist.booking.complete', $booking->id) }}">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[10px] uppercase rounded-xl tracking-wider transition shadow-sm">
                                                        Selesaikan
                                                    </button>
                                                </form>
                                            @elseif ($booking->status === 'completed')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xxs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">Selesai</span>
                                            @elseif ($booking->status === 'cancelled')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xxs font-extrabold bg-stone-100 text-stone-500 border border-stone-200 uppercase tracking-wider">Batal</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 text-stone-400 text-xs bg-stone-50/30 rounded-2xl border border-dashed border-stone-200">
                                    Tidak ada jadwal potong rambut pada tanggal ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Performance and Profile form container -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- Performance statistics (Productivity metrics, no financial fields) -->
                        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                            <h4 class="font-extrabold text-stone-900 text-xs uppercase tracking-wider border-b border-stone-100 pb-3">Ringkasan Kinerja & Produktivitas</h4>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-3 bg-stone-50 rounded-xl border border-stone-100 text-center">
                                        <span class="text-[9px] text-stone-400 block font-bold uppercase">Booking Bulan Ini</span>
                                        <span class="text-base font-extrabold text-[#0A3D91]">{{ $mtdBookingsCount }}</span>
                                    </div>
                                    <div class="p-3 bg-stone-50 rounded-xl border border-stone-100 text-center">
                                        <span class="text-[9px] text-stone-400 block font-bold uppercase">Total Selesai</span>
                                        <span class="text-base font-extrabold text-stone-850">{{ $totalCompleted }}</span>
                                    </div>
                                </div>

                                <!-- Performance visual chart representation -->
                                <div class="pt-2">
                                    <span class="text-[10px] text-stone-400 block font-bold uppercase mb-3">Grafik Pesanan Selesai (7 Hari Terakhir)</span>
                                    <div class="flex items-end justify-between h-24 px-2 pt-2 border-b border-stone-200 bg-stone-50/30 rounded-xl p-3">
                                        @foreach($chartData as $data)
                                            <div class="flex flex-col items-center flex-1 space-y-1">
                                                <div class="w-4.5 bg-brand-500 rounded-t transition-all duration-300 hover:bg-brand-600" style="height: {{ max($data['count'] * 15, 6) }}px;" title="{{ $data['count'] }} Booking"></div>
                                                <span class="text-[9px] font-mono text-stone-400 font-extrabold">{{ $data['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Form -->
                        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                            <h4 class="font-extrabold text-stone-900 text-xs uppercase tracking-wider border-b border-stone-100 pb-3">Perbarui Profil Anda</h4>
                            
                            <form method="POST" action="{{ route('stylist.profile.update') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="name" value="Nama Lengkap" class="text-stone-700 font-semibold mb-1 text-[11px]" />
                                    <x-text-input name="name" id="name" value="{{ old('name', $stylist->name) }}" class="block w-full px-3 py-2 text-xs rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm bg-white" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="email" value="Alamat Email (Asli)" class="text-stone-700 font-semibold mb-1 text-[11px]" />
                                    <x-text-input name="email" id="email" value="{{ old('email', auth()->user()->email) }}" class="block w-full px-3 py-2 text-xs rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm bg-white" required type="email" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="phone" value="No. WhatsApp (Awali 62)" class="text-stone-700 font-semibold mb-1 text-[11px]" />
                                    <x-text-input name="phone" id="phone" value="{{ old('phone', $stylist->phone) }}" class="block w-full px-3 py-2 text-xs rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm bg-white" required placeholder="628123456789" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="specialization" value="Spesialisasi" class="text-stone-700 font-semibold mb-1 text-[11px]" />
                                    <x-text-input name="specialization" id="specialization" value="{{ old('specialization', $stylist->specialization) }}" class="block w-full px-3 py-2 text-xs rounded-xl border border-stone-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm bg-white" required placeholder="Contoh: Hair Coloring, Fade Expert" />
                                    <x-input-error :messages="$errors->get('specialization')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="bio" value="Biografi Singkat" class="text-stone-700 font-semibold mb-1 text-[11px]" />
                                    <textarea name="bio" id="bio" rows="3" class="block w-full text-xs border-stone-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-sm px-3 py-2 bg-white">{{ old('bio', $stylist->bio) }}</textarea>
                                    <x-input-error :messages="$errors->get('bio')" class="mt-1" />
                                </div>

                                <div class="pt-2">
                                    <x-primary-button class="w-full justify-center py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow transition duration-150 text-xs">
                                        Simpan Perubahan
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    @endif
@endsection
