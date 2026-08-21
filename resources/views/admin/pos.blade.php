@extends('layouts.admin')

@section('page_title')
    POS Cashier System
@endsection

@section('content')
<div>
    @if(session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif
    @if(session()->has('error'))
        <x-ui.alert variant="danger" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 font-sans">
        <!-- Col 1 (4 cols): Services / Products & Checked-in Bookings -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Active Checked-In Bookings -->
            <div class="bg-white border border-stone-200 p-5 rounded-3xl shadow-sm">
                <h3 class="font-black text-stone-900 text-xs uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-ping"></span>
                    Antrean Kasir (Checked-In)
                </h3>
                <div class="space-y-3 overflow-y-auto max-h-[180px] pr-1.5">
                    @forelse($pendingBookings as $pBook)
                        <div onclick="selectBooking({{ $pBook->id }})" id="booking-item-{{ $pBook->id }}" class="booking-queue-item p-3.5 rounded-2xl cursor-pointer border transition border-stone-150 hover:border-stone-300 bg-stone-50/20">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-xs text-stone-900">{{ $pBook->customer->name }}</h4>
                                    <span class="text-[10px] text-stone-400 font-mono mt-0.5 block">{{ $pBook->booking_code }}</span>
                                </div>
                                <span class="px-2 py-0.5 bg-blue-55 text-blue-700 rounded text-[9px] font-black uppercase font-mono">{{ $pBook->status }}</span>
                            </div>
                            <div class="mt-2 text-[10px] text-stone-500">
                                <strong>Layanan:</strong> {{ $pBook->items->first()?->service?->name ?? 'Signature Haircut' }}
                            </div>
                        </div>
                    @empty
                        <p class="text-stone-400 text-xxs py-4 text-center select-none">Tidak ada booking yang sedang menunggu pembayaran.</p>
                    @endforelse
                </div>
            </div>

            <!-- Product & Service Catalogue -->
            <div class="bg-white border border-stone-200 p-5 rounded-3xl shadow-sm space-y-4">
                <div class="border-b pb-2 flex justify-between items-center">
                    <h3 class="font-black text-stone-900 text-xs uppercase tracking-wider">Katalog Jasa & Produk</h3>
                </div>

                <!-- Services Tab -->
                <div class="space-y-3">
                    <form method="GET" action="{{ route('admin.pos') }}">
                        <input type="hidden" name="outlet_id" value="{{ $selectedOutletId }}">
                        <input type="text" name="search_service" value="{{ $searchService }}" placeholder="Cari Layanan Jasa..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </form>
                    <div class="space-y-2 overflow-y-auto max-h-[160px] pr-1.5">
                        @forelse($services as $serv)
                            <div onclick="addToCart('service', '{{ $serv->id }}', '{{ addslashes($serv->name) }}', '{{ $serv->default_price }}')" class="p-2.5 rounded-xl cursor-pointer border border-stone-150 hover:border-stone-300 bg-stone-50/10 flex justify-between items-center text-xs transition">
                                <span class="font-medium text-stone-800">{{ $serv->name }}</span>
                                <span class="font-bold text-stone-900 font-mono bg-stone-50 border px-2 py-0.5 rounded">Rp {{ number_format($serv->default_price, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-stone-400 text-xxs py-2">Jasa tidak ditemukan.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Products Tab -->
                <div class="space-y-3 pt-3 border-t">
                    <form method="GET" action="{{ route('admin.pos') }}">
                        <input type="hidden" name="outlet_id" value="{{ $selectedOutletId }}">
                        <input type="text" name="search_product" value="{{ $searchProduct }}" placeholder="Cari Produk Ritel..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </form>
                    <div class="space-y-2 overflow-y-auto max-h-[160px] pr-1.5">
                        @forelse($products as $prod)
                            <div onclick="addToCart('product', '{{ $prod->id }}', '{{ addslashes($prod->name) }}', '{{ $prod->selling_price }}')" class="p-2.5 rounded-xl cursor-pointer border border-stone-150 hover:border-stone-300 bg-stone-50/10 flex justify-between items-center text-xs transition">
                                <div>
                                    <span class="font-medium text-stone-800 block">{{ $prod->name }}</span>
                                    <span class="text-[9px] text-stone-400 font-mono mt-0.5 block">SKU: {{ $prod->sku }} | Stok: {{ $prod->stock }}</span>
                                </div>
                                <span class="font-bold text-stone-900 font-mono bg-stone-50 border px-2 py-0.5 rounded">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-stone-400 text-xxs py-2">Produk tidak ditemukan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Col 2 (5 cols): Current Cart List -->
        <div class="lg:col-span-5 space-y-6">
            <form method="POST" action="{{ route('admin.pos.checkout') }}" id="checkout-form" class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm space-y-4">
                @csrf
                <input type="hidden" name="outlet_id" value="{{ $selectedOutletId }}">
                <input type="hidden" name="booking_id" id="checkout_booking_id">
                <input type="hidden" name="cart_json" id="cart_json">

                <h3 class="font-black text-stone-900 text-xs uppercase tracking-wider border-b pb-2 flex justify-between items-center">
                    <span>Keranjang Belanja POS</span>
                    <span class="font-mono text-stone-400" id="cart-count-label">Total Items: 0</span>
                </h3>

                <!-- Cart container dynamically updated by JS -->
                <div id="cart-items-container" class="space-y-3 overflow-y-auto min-h-[380px] max-h-[380px] pr-1.5">
                    <div class="text-center py-20 text-stone-400 flex flex-col items-center justify-center min-h-[380px]">
                        <div class="h-12 w-12 bg-stone-50 border rounded-full flex items-center justify-center mb-2">
                            <svg class="w-6 h-6 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-xs">Keranjang kasir masih kosong.</span>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Catatan Struk Transaksi</label>
                    <textarea name="notes" placeholder="Masukkan catatan tambahan..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 p-2.5 h-16 text-stone-750 focus:border-[#0A3D91] transition resize-none"></textarea>
                </div>
            </form>
        </div>

        <!-- Col 3 (3 cols): Payment and Checkout parameter Details -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm space-y-6">
                <h3 class="font-black text-stone-900 text-xs uppercase tracking-wider border-b pb-2">Checkout & Pembayaran</h3>

                <!-- Select Customer -->
                <div>
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Pelanggan (Customer)</label>
                    <select name="customer_id" form="checkout-form" id="customer_id_select" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700">
                        <option value="">-- Guest (Umum) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Stylist / Barber -->
                <div>
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Stylist / Barber pelaksana</label>
                    <select name="staff_id" form="checkout-form" id="staff_id_select" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700">
                        <option value="">-- Pilih Stylist --</option>
                        @foreach($stylists as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment calculations -->
                <div class="space-y-2 border-t pt-4">
                    <div class="flex justify-between text-xxs font-bold text-stone-500">
                        <span>Subtotal Keranjang</span>
                        <span class="font-mono" id="subtotal-label">Rp 0</span>
                    </div>

                    <!-- Discount input -->
                    <div class="flex justify-between items-center text-xxs font-bold text-stone-500">
                        <span>Diskon Tambahan</span>
                        <input type="number" name="discount" form="checkout-form" id="discount-input" oninput="updateGlobalDiscount(this.value)" value="0" class="w-24 text-right text-xs font-mono font-bold rounded-lg border-stone-200 h-7 px-2 text-stone-900" min="0" />
                    </div>

                    <div class="flex justify-between text-xxs font-bold text-stone-500">
                        <span>Pajak (PPN 10%)</span>
                        <span class="font-mono text-stone-400" id="tax-label">Rp 0</span>
                    </div>

                    <div class="flex justify-between items-end border-t pt-3">
                        <span class="text-xs font-black text-stone-900 uppercase">Grand Total</span>
                        <span class="text-base font-black text-[#0A3D91] font-mono" id="grandtotal-label">Rp 0</span>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="space-y-3 pt-3 border-t">
                    <div id="online-paid-alert" class="hidden p-3.5 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-800 text-xxs font-bold flex flex-col items-center justify-center gap-1.5 text-center">
                        <span class="uppercase font-black text-[9px] tracking-wider">Lunas (Online Gateway)</span>
                        <span class="text-[9px] font-sans text-emerald-600 font-normal leading-relaxed">Pembayaran telah terkonfirmasi lunas melalui Payment Gateway saat reservasi online.</span>
                    </div>

                    <div id="payment-methods-selection" class="space-y-3">
                        <label class="block text-[10px] uppercase font-bold text-stone-400">Metode Bayar</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['cash' => 'Cash', 'qris' => 'QRIS', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet'] as $mKey => $mName)
                                <div onclick="selectPaymentMethod('{{ $mKey }}')" id="pay-btn-{{ $mKey }}" class="payment-method-btn p-2 rounded-xl text-center cursor-pointer border text-xxs font-bold transition border-stone-150 bg-stone-50/10 text-stone-600">
                                    {{ $mName }}
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="payment_method" form="checkout-form" id="payment_method_input" value="cash">

                        <!-- Reference Code -->
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Ref Transaksi / Nomor EDC</label>
                            <input type="text" name="transaction_reference" form="checkout-form" id="transaction_reference" placeholder="e.g. REF-12345" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <button type="submit" form="checkout-form" class="w-full h-11 bg-[#0A3D91] hover:bg-blue-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm">
                        Proses Checkout & Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable Thermal Receipt Modal -->
    @if($lastTransaction)
        <div id="receiptModal" class="fixed inset-0 z-50 overflow-y-auto bg-stone-950/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white border rounded-3xl p-6 max-w-sm w-full shadow-2xl space-y-6">
                <!-- Struk Area (Printable) -->
                <div id="receipt-print" class="receipt-thermal p-4 border-dashed border-2 border-stone-300 font-mono text-xxs text-stone-850 space-y-4">
                    <div class="text-center space-y-1">
                        <h2 class="text-base font-black uppercase text-stone-900">MORE HAIR STUDIO</h2>
                        <p class="text-[10px]">{{ $lastTransaction->outlet->name }}</p>
                        <p class="text-[9px] text-stone-500">Invoice: {{ $lastTransaction->transaction_number }}</p>
                    </div>

                    <div class="border-b border-dashed pb-2 space-y-1">
                        <div class="flex justify-between">
                            <span>Waktu:</span>
                            <span>{{ $lastTransaction->completed_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kasir:</span>
                            <span>{{ auth()->user()->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pelanggan:</span>
                            <span>{{ $lastTransaction->customer ? $lastTransaction->customer->name : 'Guest' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pembayaran:</span>
                            <span>{{ $lastTransaction->payment_method === 'gateway' ? 'Online Gateway (Lunas)' : strtoupper($lastTransaction->payment_method) }}</span>
                        </div>
                        @if($lastTransaction->stylist)
                            <div class="flex justify-between">
                                <span>Stylist:</span>
                                <span>{{ $lastTransaction->stylist->name }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Items -->
                    <div class="border-b border-dashed pb-2 space-y-2">
                        @foreach($lastTransaction->items as $tItem)
                            <div class="space-y-1">
                                <div class="flex justify-between font-bold">
                                    <span>[{{ strtoupper($tItem->item_type) }}] {{ $tItem->item_type === 'service' ? $tItem->service?->name : $tItem->product?->name }}</span>
                                    <span>x{{ $tItem->quantity }}</span>
                                </div>
                                <div class="flex justify-between text-stone-500">
                                    <span>@ Rp {{ number_format($tItem->unit_price, 0, ',', '.') }}</span>
                                    <span>Rp {{ number_format($tItem->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Totals -->
                    <div class="space-y-1 pt-1 text-right">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($lastTransaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($lastTransaction->discount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Diskon:</span>
                                <span>-Rp {{ number_format($lastTransaction->discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>PPN (10%):</span>
                            <span>Rp {{ number_format($lastTransaction->tax, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-black text-stone-900 border-t border-dashed pt-2">
                            <span>GRAND TOTAL:</span>
                            <span>Rp {{ number_format($lastTransaction->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="text-center pt-4 border-t border-dashed space-y-1 text-stone-500">
                        <p class="font-bold text-stone-850">TERIMA KASIH</p>
                        <p>Silakan berkunjung kembali!</p>
                        <p class="text-[8px] font-sans">Powered by MORE CRM-POS System</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeReceiptModal()" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-750 text-xxs font-bold uppercase tracking-wider rounded-xl transition">Tutup</button>
                    <button onclick="window.print()" class="h-9 px-5 text-xxs font-bold uppercase tracking-wider bg-emerald-600 text-white hover:bg-emerald-700 transition rounded-xl shadow-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>

        <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                #receipt-print, #receipt-print * {
                    visibility: visible;
                }
                #receipt-print {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 80mm;
                    margin: 0;
                    padding: 10px;
                    border: none;
                }
            }
        </style>
    @endif
</div>

<script>
    // Global Cart State
    let cart = [];
    let globalDiscount = 0;

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const countLabel = document.getElementById('cart-count-label');
        
        countLabel.innerText = 'Total Items: ' + cart.length;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-20 text-stone-400 flex flex-col items-center justify-center min-h-[380px]">
                    <div class="h-12 w-12 bg-stone-50 border rounded-full flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-xs">Keranjang kasir masih kosong.</span>
                </div>
            `;
            document.getElementById('cart_json').value = '';
            recalculateTotals();
            return;
        }

        let html = '';
        cart.forEach((item, index) => {
            html += `
                <div class="p-4 rounded-2xl border border-stone-150 bg-stone-50/30 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase font-mono \${item.type === 'service' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700'\}">\${item.type\}</span>
                            <h4 class="font-bold text-xs text-stone-900 mt-1">\${item.name\}</h4>
                        </div>
                        <button type="button" onclick="removeFromCart(\${index\})" class="text-red-550 hover:text-red-700 text-xs">Hapus</button>
                    </div>

                    <div class="grid grid-cols-3 gap-3 items-end">
                        <!-- Price -->
                        <div>
                            <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">Harga Satuan</span>
                            <span class="font-mono text-xs text-stone-800">Rp \${new Intl.NumberFormat('id-ID').format(item.price)\}</span>
                        </div>

                        <!-- Qty input -->
                        <div>
                            <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">Kuantitas</span>
                            <input type="number" value="\${item.qty\}" onchange="updateQty(\${index\}, this.value)" class="w-full text-xs font-mono font-bold rounded-lg border-stone-200 h-8 px-2 text-stone-900" min="1" />
                        </div>

                        <!-- Subtotal -->
                        <div class="text-right">
                            <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">Subtotal</span>
                            <span class="font-mono font-bold text-xs text-stone-900">Rp \${new Intl.NumberFormat('id-ID').format((item.price * item.qty) - item.discount)\}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('cart_json').value = JSON.stringify(cart);
        recalculateTotals();
    }

    function addToCart(type, id, name, price) {
        let existing = cart.find(item => item.type === type && item.id == id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({
                type: type,
                id: id,
                name: name,
                price: parseFloat(price),
                qty: 1,
                discount: 0
            });
        }
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function updateQty(index, qty) {
        qty = parseInt(qty);
        if (isNaN(qty) || qty < 1) qty = 1;
        cart[index].qty = qty;
        renderCart();
    }

    function updateGlobalDiscount(val) {
        globalDiscount = parseFloat(val);
        if (isNaN(globalDiscount) || globalDiscount < 0) globalDiscount = 0;
        recalculateTotals();
    }

    function recalculateTotals() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += (item.price * item.qty) - item.discount;
        });

        let tax = Math.round(subtotal * 0.10);
        let grandtotal = Math.max(0, subtotal - globalDiscount + tax);

        document.getElementById('subtotal-label').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        document.getElementById('tax-label').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(tax);
        document.getElementById('grandtotal-label').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandtotal);
    }

    function selectBooking(bookingId) {
        // Toggle selected styling in queue list
        document.querySelectorAll('.booking-queue-item').forEach(item => {
            item.classList.remove('border-[#0A3D91]', 'bg-blue-50/20');
            item.classList.add('border-stone-150', 'bg-stone-50/20');
        });
        
        const el = document.getElementById('booking-item-' + bookingId);
        if (el) {
            el.classList.remove('border-stone-150', 'bg-stone-50/20');
            el.classList.add('border-[#0A3D91]', 'bg-blue-50/20');
        }

        // Fetch booking details from traditional JSON API
        fetch('/admin/pos/booking/' + bookingId)
            .then(res => res.json())
            .then(data => {
                document.getElementById('checkout_booking_id').value = data.id;
                document.getElementById('customer_id_select').value = data.customer_id || '';
                document.getElementById('staff_id_select').value = data.stylist_id || '';
                document.getElementById('discount-input').value = data.discount_amount || 0;
                globalDiscount = data.discount_amount || 0;

                cart = data.items;

                // Handle payment method display if already paid online
                const gatewayAlert = document.getElementById('online-paid-alert');
                const normalSelector = document.getElementById('payment-methods-selection');
                
                if (data.paid_online) {
                    gatewayAlert.classList.remove('hidden');
                    normalSelector.classList.add('hidden');
                    selectPaymentMethod('gateway');
                } else {
                    gatewayAlert.classList.add('hidden');
                    normalSelector.classList.remove('hidden');
                    selectPaymentMethod('cash');
                }

                renderCart();
            });
    }

    function selectPaymentMethod(method) {
        document.getElementById('payment_method_input').value = method;
        
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('border-[#0A3D91]', 'bg-blue-50/20', 'text-[#0A3D91]');
            btn.classList.add('border-stone-150', 'bg-stone-50/10', 'text-stone-600');
        });

        const activeBtn = document.getElementById('pay-btn-' + method);
        if (activeBtn) {
            activeBtn.classList.remove('border-stone-150', 'bg-stone-50/10', 'text-stone-600');
            activeBtn.classList.add('border-[#0A3D91]', 'bg-blue-50/20', 'text-[#0A3D91]');
        }
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').style.display = 'none';
    }

    // Default select method cash
    selectPaymentMethod('cash');
</script>
@endsection
