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

    <div class="space-y-6 font-sans">
        <!-- Header Filters Card -->
        <div class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-black text-stone-900 uppercase tracking-tight">Riwayat Transaksi POS</h2>
                    <p class="text-xs text-stone-500 font-medium">Lacak semua pembayaran kasir, cetak ulang struk, dan kelola otorisasi pengembalian dana (refund).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="sm:col-span-2">
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Cari Faktur / Customer</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="e.g. TX-2026... atau Budi" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-750 placeholder-stone-400 focus:border-[#0A3D91] transition" />
                </div>

                <!-- Outlet Scope -->
                <div>
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Scope Outlet</label>
                    <select wire:model.live="filterOutlet" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700" {{ auth()->user()->role === 'outlet_admin' ? 'disabled' : '' }}>
                        <option value="">Semua Outlet</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->id }}">{{ $o->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cara Bayar -->
                <div>
                    <label class="block text-[10px] uppercase font-bold text-stone-400 mb-1">Metode Pembayaran</label>
                    <select wire:model.live="filterPaymentMethod" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-3 text-stone-700">
                        <option value="">Semua Metode</option>
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Bank Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div class="grid grid-cols-2 gap-2 sm:col-span-1">
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Dari</label>
                        <input type="date" wire:model.live="dateFrom" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-2 text-stone-700" />
                    </div>
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-stone-400 mb-1">Sampai</label>
                        <input type="date" wire:model.live="dateTo" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 h-9 px-2 text-stone-700" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table Card -->
        <div class="bg-white border border-stone-200 p-6 rounded-3xl shadow-sm space-y-4">
            <div class="overflow-x-auto rounded-2xl border border-stone-150">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-150 text-stone-500 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-32">Waktu</th>
                            <th class="py-3.5 px-4">No. Transaksi</th>
                            <th class="py-3.5 px-4">Outlet</th>
                            <th class="py-3.5 px-4">Pelanggan</th>
                            <th class="py-3.5 px-4">Total Bayar</th>
                            <th class="py-3.5 px-4 text-center">Metode</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white text-stone-700">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-stone-50/50 transition">
                                <td class="py-3 px-4 font-mono text-[10px] text-stone-400">{{ $tx->completed_at ? $tx->completed_at->format('d/m/Y H:i') : $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-stone-900">{{ $tx->transaction_number }}</td>
                                <td class="py-3 px-4 text-stone-600">{{ $tx->outlet->name }}</td>
                                <td class="py-3 px-4 font-bold text-stone-800">{{ $tx->customer ? $tx->customer->name : 'Guest (Tamu)' }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-stone-900">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase font-mono bg-blue-50 text-blue-700">
                                        {{ $tx->payment_method ?: 'Cash' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($tx->status === 'completed')
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[9px] font-black uppercase font-mono">Selesai</span>
                                    @elseif($tx->status === 'refunded')
                                        <span class="px-2 py-0.5 bg-red-50 text-red-750 rounded text-[9px] font-black uppercase font-mono">Refunded</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-stone-100 text-stone-550 rounded text-[9px] font-black uppercase font-mono">{{ $tx->status }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <x-ui.button variant="outline" size="sm" wire:click="viewReceipt({{ $tx->id }})" class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Struk
                                    </x-ui.button>
                                    
                                    @if($tx->status === 'completed' && auth()->user()->role === 'super_admin')
                                        <button onclick="confirm('Apakah Anda yakin ingin membatalkan & me-refund transaksi POS ini? Stok produk dan poin pelanggan akan ditarik kembali.') && @this.processRefund({{ $tx->id }})" class="h-7 px-3 text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-750 hover:bg-red-100 transition rounded-lg">
                                            Refund
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-stone-400 text-xs">Belum ada riwayat transaksi tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- Printable Thermal Receipt Modal -->
    @if($showReprintModal && $selectedTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-stone-950/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white border rounded-3xl p-6 max-w-sm w-full shadow-2xl space-y-6">
                <!-- Struk Area (Printable) -->
                <div id="receipt-reprint" class="receipt-thermal p-4 border-dashed border-2 border-stone-300 font-mono text-xxs text-stone-850 space-y-4">
                    <div class="text-center space-y-1">
                        <h2 class="text-base font-black uppercase text-stone-900">MORE HAIR STUDIO</h2>
                        <p class="text-[10px]">{{ $selectedTransaction->outlet->name }}</p>
                        <p class="text-[9px] text-stone-500">Invoice: {{ $selectedTransaction->transaction_number }}</p>
                    </div>

                    <div class="border-b border-dashed pb-2 space-y-1">
                        <div class="flex justify-between">
                            <span>Waktu:</span>
                            <span>{{ $selectedTransaction->completed_at ? $selectedTransaction->completed_at->format('d/m/Y H:i') : $selectedTransaction->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Status:</span>
                            <span class="font-bold uppercase text-stone-900">{{ $selectedTransaction->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pelanggan:</span>
                            <span>{{ $selectedTransaction->customer ? $selectedTransaction->customer->name : 'Guest' }}</span>
                        </div>
                        @if($selectedTransaction->stylist)
                            <div class="flex justify-between">
                                <span>Stylist:</span>
                                <span>{{ $selectedTransaction->stylist->name }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Items -->
                    <div class="border-b border-dashed pb-2 space-y-2">
                        @foreach($selectedTransaction->items as $tItem)
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
                            <span>Rp {{ number_format($selectedTransaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($selectedTransaction->discount > 0)
                            <div class="flex justify-between text-red-650">
                                <span>Diskon:</span>
                                <span>-Rp {{ number_format($selectedTransaction->discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>PPN (10%):</span>
                            <span>Rp {{ number_format($selectedTransaction->tax, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-black text-stone-900 border-t border-dashed pt-2">
                            <span>GRAND TOTAL:</span>
                            <span>Rp {{ number_format($selectedTransaction->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="text-center pt-4 border-t border-dashed space-y-1 text-stone-500">
                        <p class="font-bold text-stone-850">TERIMA KASIH</p>
                        <p>Salinan Struk Transaksi Resmi</p>
                        <p class="text-[8px] font-sans">Powered by MORE CRM-POS System</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button variant="secondary" wire:click="closeReprintModal">Tutup</x-ui.button>
                    <button onclick="window.print()" class="h-9 px-5 text-xxs font-bold uppercase tracking-wider bg-emerald-600 text-white hover:bg-emerald-700 transition rounded-xl shadow-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Ulang Struk
                    </button>
                </div>
            </div>
        </div>

        <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                #receipt-reprint, #receipt-reprint * {
                    visibility: visible;
                }
                #receipt-reprint {
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
