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
                        <div wire:click="selectBooking({{ $pBook->id }})" class="p-3.5 rounded-2xl cursor-pointer border transition {{ $selectedBookingId == $pBook->id ? 'border-[#0A3D91] bg-blue-50/20' : 'border-stone-150 hover:border-stone-300 bg-stone-50/20' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-xs text-stone-900">{{ $pBook->customer->name }}</h4>
                                    <span class="text-[10px] text-stone-400 font-mono mt-0.5 block">{{ $pBook->booking_code }}</span>
                                </div>
                                <span class="px-2 py-0.5 bg-blue-55 text-blue-700 rounded text-[9px] font-black uppercase font-mono">{{ $pBook->status }}</span>
                            </div>
                            <div class="mt-2 text-[10px] text-stone-500">
                                <strong>Layanan:</strong> {{ $pBook->items->first()->service->name }}
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
                    <div>
                        <input type="text" wire:model.live.debounce.300ms="searchService" placeholder="Cari Layanan Jasa..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </div>
                    <div class="space-y-2 overflow-y-auto max-h-[160px] pr-1.5">
                        @forelse($services as $serv)
                            <div wire:click="addToCart('service', {{ $serv->id }})" class="p-2.5 rounded-xl cursor-pointer border border-stone-150 hover:border-stone-300 bg-stone-50/10 flex justify-between items-center text-xs transition">
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
                    <div>
                        <input type="text" wire:model.live.debounce.300ms="searchProduct" placeholder="Cari Produk Ritel..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                    </div>
                    <div class="space-y-2 overflow-y-auto max-h-[160px] pr-1.5">
                        @forelse($products as $prod)
                            <div wire:click="addToCart('product', {{ $prod->id }})" class="p-2.5 rounded-xl cursor-pointer border border-stone-150 hover:border-stone-300 bg-stone-50/10 flex justify-between items-center text-xs transition">
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
            <div class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm space-y-4">
                <h3 class="font-black text-stone-900 text-xs uppercase tracking-wider border-b pb-2 flex justify-between items-center">
                    <span>Keranjang Belanja POS</span>
                    <span class="font-mono text-stone-400">Total Items: {{ count($cart) }}</span>
                </h3>

                <div class="space-y-3 overflow-y-auto min-h-[380px] max-h-[380px] pr-1.5">
                    @forelse($cart as $index => $item)
                        <div class="p-4 rounded-2xl border border-stone-150 bg-stone-50/30 space-y-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase font-mono {{ $item['type'] === 'service' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700' }}">{{ $item['type'] }}</span>
                                    <h4 class="font-bold text-xs text-stone-900 mt-1">{{ $item['name'] }}</h4>
                                </div>
                                <button wire:click="removeFromCart({{ $index }})" class="text-red-550 hover:text-red-700 text-xs">Hapus</button>
                            </div>

                            <div class="grid grid-cols-3 gap-3 items-end">
                                <!-- Price -->
                                <div>
                                    <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">Harga Satuan</span>
                                    <span class="font-mono text-xs text-stone-800">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                </div>

                                <!-- Qty input -->
                                <div>
                                    <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">Kuantitas</span>
                                    <input type="number" value="{{ $item['qty'] }}" wire:change="updateQty({{ $index }}, $event.target.value)" class="w-full text-xs font-mono font-bold rounded-lg border-stone-200 h-8 px-2 text-stone-900" min="1" />
                                </div>

                                <!-- Subtotal -->
                                <div class="text-right">
                                    <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">Subtotal</span>
                                    <span class="font-mono font-bold text-xs text-stone-900">Rp {{ number_format(($item['price'] * $item['qty']) - $item['discount'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-20 text-stone-400 flex flex-col items-center justify-center min-h-[380px]">
                            <div class="h-12 w-12 bg-stone-50 border rounded-full flex items-center justify-center mb-2">
                                <svg class="w-6 h-6 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <span class="text-xs">Keranjang kasir masih kosong.</span>
                        </div>
                    @endforelse
                </div>

                <div class="pt-4 border-t">
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Catatan Struk Transaksi</label>
                    <textarea wire:model="notes" placeholder="Masukkan catatan tambahan..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 p-2.5 h-16 text-stone-700 focus:border-[#0A3D91] transition resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- Col 3 (3 cols): Payment and Checkout parameter Details -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm space-y-6">
                <h3 class="font-black text-stone-900 text-xs uppercase tracking-wider border-b pb-2">Checkout & Pembayaran</h3>

                <!-- Select Customer -->
                <div>
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Pelanggan (Customer)</label>
                    <select wire:model="selectedCustomerId" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700">
                        <option value="">-- Guest (Umum) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Stylist / Barber -->
                <div>
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Stylist / Barber pelaksana</label>
                    <select wire:model="selectedStaffId" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700">
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
                        <span class="font-mono">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>

                    <!-- Discount input -->
                    <div class="flex justify-between items-center text-xxs font-bold text-stone-500">
                        <span>Diskon Tambahan</span>
                        <input type="number" wire:model.live="cartDiscount" class="w-24 text-right text-xs font-mono font-bold rounded-lg border-stone-200 h-7 px-2 text-stone-900" min="0" />
                    </div>

                    <div class="flex justify-between text-xxs font-bold text-stone-500">
                        <span>Pajak (PPN 10%)</span>
                        <span class="font-mono text-stone-400">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-end border-t pt-3">
                        <span class="text-xs font-black text-stone-900 uppercase">Grand Total</span>
                        <span class="text-base font-black text-[#0A3D91] font-mono">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Payment Methods -->
                @if($bookingPaidOnline)
                    <div class="space-y-3 pt-3 border-t">
                        <div class="p-3.5 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-800 text-xxs font-bold flex flex-col items-center justify-center gap-1.5 text-center">
                            <span class="uppercase font-black text-[9px] tracking-wider">Lunas (Online Gateway)</span>
                            <span class="text-[9px] font-sans text-emerald-600 font-normal leading-relaxed">Pembayaran telah terkonfirmasi lunas melalui Payment Gateway saat reservasi online.</span>
                        </div>
                    </div>
                @else
                    <div class="space-y-3 pt-3 border-t">
                        <label class="block text-[10px] uppercase font-bold text-stone-400">Metode Bayar</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['cash' => 'Cash', 'qris' => 'QRIS', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet'] as $mKey => $mName)
                                <div wire:click="$set('paymentMethod', '{{ $mKey }}')" class="p-2 rounded-xl text-center cursor-pointer border text-xxs font-bold transition {{ $paymentMethod === $mKey ? 'border-[#0A3D91] bg-blue-50/20 text-[#0A3D91]' : 'border-stone-150 bg-stone-50/10 text-stone-600' }}">
                                    {{ $mName }}
                                </div>
                            @endforeach
                        </div>

                        <!-- Reference Code -->
                        <div>
                            <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Ref Transaksi / Nomor EDC</label>
                            <input type="text" wire:model="transactionReference" placeholder="e.g. REF-12345" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-8 px-3 text-stone-750 focus:border-[#0A3D91] transition" />
                        </div>
                    </div>
                @endif

                <div class="pt-4 border-t">
                    <button wire:click="checkout" class="w-full h-11 bg-[#0A3D91] hover:bg-blue-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm">
                        {{ $bookingPaidOnline ? 'Selesaikan Kunjungan & Cetak Struk' : 'Proses Checkout & Bayar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable Thermal Receipt Modal -->
    @if($showSuccessReceipt && $lastTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-stone-950/40 backdrop-blur-xs flex items-center justify-center p-4">
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
                                    <span>[{{ strtoupper($tItem->item_type) }}] {{ $tItem->item_type === 'service' ? $tItem->service->name : $tItem->product->name }}</span>
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
                    <x-ui.button variant="secondary" wire:click="closeReceiptModal">Tutup</x-ui.button>
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
