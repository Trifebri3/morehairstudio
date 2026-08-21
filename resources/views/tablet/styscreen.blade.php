@extends('layouts.tablet')

@section('content')
<div class="h-full flex flex-col space-y-6">
    <!-- Page Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-stone-200 pb-5">
        <div>
            <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight uppercase">STYSCREEN - MONITOR KASIR</h2>
            <p class="text-xs text-stone-500 font-medium mt-1">
                Outlet: <strong class="text-[#0A3D91]">{{ $outlet->name }}</strong> | Kelola pembayaran EDC/Tunai, pantau status pengerjaan, dan cetak invoice.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Search bar -->
            <form method="GET" action="{{ route('tablet.styscreen') }}" class="relative w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input 
                    type="text" 
                    name="searchQuery"
                    placeholder="Cari nama/HP/kode..." 
                    value="{{ $searchQuery }}"
                    onchange="this.form.submit()"
                    class="w-full pl-9 pr-4 py-2 border border-stone-200 rounded-xl text-xs focus:ring-[#0A3D91] focus:border-[#0A3D91] outline-none"
                />
            </form>

            <!-- Logout button -->
            <form method="POST" action="{{ route('tablet.styscreen.logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-stone-200 rounded-xl text-xs font-bold text-stone-600 hover:text-red-600 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Message -->
    @if(session()->has('message'))
        <div class="mb-4">
            <x-ui.alert variant="success" title="Pembayaran Berhasil">
                {{ session('message') }}
            </x-ui.alert>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4">
            <x-ui.alert variant="danger">
                {{ session('error') }}
            </x-ui.alert>
        </div>
    @endif

    <!-- Main Dashboard Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-grow items-stretch">
        
        <!-- Lane 1: BELUM BAYAR (Pending Payment) -->
        <div class="glass-panel p-6 rounded-3xl bg-white border border-stone-200 flex flex-col space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-red-600 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-ping"></span>
                    Belum Bayar (EDC/Tunai)
                </h3>
                <span class="px-2 py-0.5 bg-red-50 text-red-700 text-xxs font-black rounded-full">{{ $unpaidBookings->count() }}</span>
            </div>

            <div class="space-y-4 overflow-y-auto max-h-[600px] pr-1 flex-grow">
                @forelse($unpaidBookings as $b)
                    @php
                        $firstItem = $b->items->first();
                        $serviceName = $firstItem ? $firstItem->service?->name : '-';
                    @endphp
                    <div class="border border-stone-150 rounded-2xl p-4 bg-stone-50/50 hover:bg-white hover:shadow-sm transition space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-mono text-xs font-bold text-stone-900 block">{{ $b->booking_code }}</span>
                                <span class="text-stone-400 text-[10px] uppercase font-extrabold tracking-wide">{{ $b->booking_date->format('d M Y') }} @ {{ substr($firstItem?->start_time ?? '00:00:00', 0, 5) }}</span>
                            </div>
                            <x-ui.badge variant="{{ $b->status === 'checked_in' || $b->status === 'in_progress' ? 'success' : 'neutral' }}" class="text-[9px] uppercase tracking-wide">
                                {{ $b->status }}
                            </x-ui.badge>
                        </div>

                        <div class="text-xs space-y-1 text-stone-700">
                            <p><strong>Customer:</strong> {{ $b->customer->name }} ({{ $b->customer->phone }})</p>
                            <p><strong>Layanan:</strong> {{ $serviceName }}</p>
                            <p><strong>Stylist:</strong> {{ $b->stylist?->name ?? 'Any Stylist' }}</p>
                            <p class="text-sm font-bold text-[#0A3D91] mt-2">Total: Rp {{ number_format($b->net_amount, 0, ',', '.') }}</p>
                        </div>

                        <div class="pt-2">
                            <a href="?{{ http_build_query(array_merge(request()->query(), ['pay_booking_id' => $b->id])) }}" class="block w-full text-center py-2.5 rounded-xl bg-[#0A3D91] text-white hover:bg-blue-800 transition text-xxs font-black uppercase tracking-wider">
                                Bayar via EDC / Tunai
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-stone-400 text-xs text-center py-10 select-none">Tidak ada tagihan tertunda.</p>
                @endforelse
            </div>
        </div>

        <!-- Lane 2: PROSES / SEDANG TREATMENT (Paid & Serving) -->
        <div class="glass-panel p-6 rounded-3xl bg-white border border-stone-200 flex flex-col space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-blue-600 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-pulse"></span>
                    Sedang Pengerjaan / Lunas Online
                </h3>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xxs font-black rounded-full">{{ $paidActiveBookings->count() }}</span>
            </div>

            <div class="space-y-4 overflow-y-auto max-h-[600px] pr-1 flex-grow">
                @forelse($paidActiveBookings as $b)
                    @php
                        $firstItem = $b->items->first();
                        $serviceName = $firstItem ? $firstItem->service?->name : '-';
                    @endphp
                    <div class="border border-stone-150 rounded-2xl p-4 bg-stone-50/50 hover:bg-white hover:shadow-sm transition space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-mono text-xs font-bold text-stone-900 block">{{ $b->booking_code }}</span>
                                <span class="text-stone-400 text-[10px] uppercase font-extrabold tracking-wide">{{ $b->booking_date->format('d M Y') }} @ {{ substr($firstItem?->start_time ?? '00:00:00', 0, 5) }}</span>
                            </div>
                            <x-ui.badge variant="success" class="text-[9px] uppercase tracking-wide">
                                LUNAS / {{ $b->status }}
                            </x-ui.badge>
                        </div>

                        <div class="text-xs space-y-1 text-stone-700">
                            <p><strong>Customer:</strong> {{ $b->customer->name }}</p>
                            <p><strong>Layanan:</strong> {{ $serviceName }}</p>
                            <p><strong>Stylist:</strong> {{ $b->stylist?->name ?? 'Any Stylist' }}</p>
                            <p class="text-sm font-bold text-emerald-600 mt-2">Terbayar Online: Rp {{ number_format($b->net_amount, 0, ',', '.') }}</p>
                        </div>

                        <div class="pt-2 flex gap-2">
                            <a href="?{{ http_build_query(array_merge(request()->query(), ['pay_booking_id' => $b->id])) }}" class="block w-full text-center py-2.5 rounded-xl border border-stone-200 hover:bg-stone-50 text-xxs font-black uppercase tracking-wider transition">
                                Cetak Invoice
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-stone-400 text-xs text-center py-10 select-none">Tidak ada transaksi aktif yang lunas.</p>
                @endforelse
            </div>
        </div>

        <!-- Lane 3: SELESAI & LUNAS (History Today) -->
        <div class="glass-panel p-6 rounded-3xl bg-white border border-stone-200 flex flex-col space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-emerald-600 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                    Selesai & Lunas Hari Ini
                </h3>
                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xxs font-black rounded-full">{{ $completedBookings->count() }}</span>
            </div>

            <div class="space-y-4 overflow-y-auto max-h-[600px] pr-1 flex-grow">
                @forelse($completedBookings as $b)
                    @php
                        $firstItem = $b->items->first();
                        $serviceName = $firstItem ? $firstItem->service?->name : '-';
                        $pm = $b->payments->first() ? $b->payments->first()->payment_method : 'online';
                    @endphp
                    <div class="border border-stone-150 rounded-2xl p-4 bg-emerald-50/10 hover:bg-white hover:shadow-sm transition space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-mono text-xs font-bold text-stone-900 block">{{ $b->booking_code }}</span>
                                <span class="text-stone-400 text-[10px] uppercase font-extrabold tracking-wide">{{ $b->booking_date->format('d M Y') }} @ {{ substr($firstItem?->start_time ?? '00:00:00', 0, 5) }}</span>
                            </div>
                            <x-ui.badge variant="neutral" class="text-[9px] uppercase tracking-wide bg-stone-100 text-stone-700 border-stone-200">
                                {{ strtoupper($pm) }} / DONE
                            </x-ui.badge>
                        </div>

                        <div class="text-xs space-y-1 text-stone-700">
                            <p><strong>Customer:</strong> {{ $b->customer->name }}</p>
                            <p><strong>Layanan:</strong> {{ $serviceName }}</p>
                            <p><strong>Stylist:</strong> {{ $b->stylist?->name ?? 'Any Stylist' }}</p>
                            <p class="text-sm font-bold text-stone-900 mt-2">Jumlah: Rp {{ number_format($b->net_amount, 0, ',', '.') }}</p>
                        </div>

                        <div class="pt-2">
                            <a href="?{{ http_build_query(array_merge(request()->query(), ['pay_booking_id' => $b->id])) }}" class="block w-full text-center py-2.5 rounded-xl border border-stone-200 hover:bg-stone-50 text-xxs font-black uppercase tracking-wider transition">
                                Cetak Invoice / Resi
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-stone-400 text-xs text-center py-10 select-none">Belum ada transaksi selesai hari ini.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- MODAL COMPONENT (Payment / Print Receipt) -->
    @if($selectedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm print:hidden">
            <div class="relative w-full max-w-lg bg-white rounded-3xl border border-stone-200 shadow-2xl p-8 space-y-6">
                <!-- Modal Close -->
                <a href="?{{ http_build_query(request()->except('pay_booking_id')) }}" class="absolute top-6 right-6 text-stone-400 hover:text-stone-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>

                @php
                    $isPaid = $selectedBooking->payments->where('status', 'paid')->isNotEmpty();
                    $pm = $selectedBooking->payments->first() ? $selectedBooking->payments->first()->payment_method : 'online';
                @endphp

                <!-- Modal Content -->
                @if(!$isPaid)
                    <!-- Unpaid: Process Payment Desk -->
                    <div class="space-y-4">
                        <div class="border-b pb-3">
                            <span class="text-[10px] font-black uppercase tracking-widest text-[#0A3D91]">Billing Desk</span>
                            <h3 class="text-lg font-bold text-stone-900">Proses Pelunasan Tagihan</h3>
                        </div>

                        <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200/50 space-y-2 text-xs text-stone-700">
                            <p><strong>Kode Booking:</strong> <span class="font-mono font-bold">{{ $selectedBooking->booking_code }}</span></p>
                            <p><strong>Customer:</strong> {{ $selectedBooking->customer->name }} ({{ $selectedBooking->customer->phone }})</p>
                            <p><strong>Total Tagihan:</strong> <strong class="text-lg text-[#0A3D91] block mt-1">Rp {{ number_format($selectedBooking->net_amount, 0, ',', '.') }}</strong></p>
                        </div>

                        <form method="POST" action="{{ route('tablet.styscreen.pay', $selectedBooking->id) }}" class="space-y-4">
                            @csrf
                            <div>
                                <x-ui.select label="Metode Pembayaran" name="payment_method" id="pay-method-select">
                                    <option value="edc">EDC (Debit/Kredit Card)</option>
                                    <option value="cash">Tunai (Cash)</option>
                                </x-ui.select>
                            </div>

                            <div>
                                <x-ui.input label="No. Referensi / EDC (Opsional)" name="transaction_reference" placeholder="e.g. Ref: 827182" />
                            </div>

                            <div class="pt-2 flex gap-3">
                                <a href="?{{ http_build_query(request()->except('pay_booking_id')) }}" class="flex-1 text-center py-3 border border-stone-250 hover:bg-stone-50 rounded-xl text-xs font-bold transition">Batal</a>
                                <x-ui.button variant="primary" type="submit" class="flex-grow rounded-xl h-[46px] font-bold">Bayar & Selesaikan</x-ui.button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Paid: Display & Print Receipt -->
                    <div class="space-y-6">
                        <div class="border-b pb-3">
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Payment Settled</span>
                            <h3 class="text-lg font-bold text-stone-900">Cetak Resi Pembayaran</h3>
                        </div>

                        <!-- Thermal Receipt View Container (Class print:block makes it active during print) -->
                        <div class="border border-stone-200 rounded-2xl p-6 bg-stone-50 font-mono text-xs space-y-4 max-w-sm mx-auto shadow-inner bg-white" id="invoice-receipt">
                            <div class="text-center space-y-1">
                                <h4 class="font-extrabold text-sm uppercase tracking-wider">MORE HAIR STUDIO</h4>
                                <p class="text-[10px] text-stone-500 uppercase">{{ $outlet->name }}</p>
                                <p class="text-[9px] text-stone-500">{{ $outlet->address }}</p>
                                <p class="text-[9px] text-stone-500">Telp: {{ $outlet->phone }}</p>
                                <div class="border-b border-dashed border-stone-300 my-2"></div>
                            </div>

                            <div class="space-y-1 text-[10px] text-stone-700">
                                <p><strong>No:</strong> {{ $selectedBooking->booking_code }}</p>
                                <p><strong>Tgl:</strong> {{ $selectedBooking->booking_date->format('d/m/Y') }} @ {{ substr($selectedBooking->items->first()?->start_time ?? '00:00:00', 0, 5) }}</p>
                                <p><strong>Kasir:</strong> {{ auth()->user()->name }}</p>
                                <p><strong>Client:</strong> {{ $selectedBooking->customer->name }}</p>
                                <p><strong>Stylist:</strong> {{ $selectedBooking->stylist?->name ?? 'Any Stylist' }}</p>
                                <div class="border-b border-dashed border-stone-300 my-2"></div>
                            </div>

                            <div class="space-y-1 text-[10px]">
                                @foreach($selectedBooking->items as $item)
                                    <div class="flex justify-between">
                                        <span class="truncate max-w-[180px]">{{ $item->service?->name }}</span>
                                        <span>Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                <div class="border-b border-dashed border-stone-300 my-2"></div>
                            </div>

                            <div class="space-y-1 text-[10px] text-stone-800">
                                @if($selectedBooking->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span>Diskon:</span>
                                        <span>-Rp {{ number_format($selectedBooking->discount_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between font-extrabold text-xs text-stone-900 pt-1">
                                    <span>TOTAL:</span>
                                    <span>Rp {{ number_format($selectedBooking->net_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-[9px] text-stone-500">
                                    <span>Metode:</span>
                                    <span>{{ strtoupper($pm) }}</span>
                                </div>
                            </div>

                            <div class="text-center pt-4 border-t border-dashed border-stone-300 text-[9px] text-stone-500 uppercase space-y-1">
                                <p>Terima kasih atas kunjungan Anda</p>
                                <p>Groomed to Perfection!</p>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <a href="?{{ http_build_query(request()->except('pay_booking_id')) }}" class="flex-1 text-center py-3 border border-stone-250 hover:bg-stone-50 rounded-xl text-xs font-bold transition">Tutup</a>
                            <x-ui.button variant="primary" type="button" class="flex-grow rounded-xl h-[46px] font-bold" onclick="window.print()">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak Invoice / Resi
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- CSS Print Media Styles for Thermal Receipt Print-Only Layout -->
    <style>
    @media print {
        body * {
            visibility: hidden;
            background: none !important;
        }
        #invoice-receipt, #invoice-receipt * {
            visibility: visible;
        }
        #invoice-receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm; /* Standard receipt width */
            padding: 5px;
            margin: 0;
            border: none !important;
            box-shadow: none !important;
            background: white !important;
        }
    }
    </style>
</div>

<script>
    // Refresh visual dashboard styscreen lanes every 10 seconds
    setTimeout(() => {
        window.location.reload();
    }, 10000);
</script>
@endsection
