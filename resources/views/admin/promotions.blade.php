@extends('layouts.admin')

@section('page_title')
    Promotions & Discounts Management
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if($isCreating || $editingPromotion)
        <!-- Create/Edit Form Card -->
        <x-ui.card subtitle="Discount Parameters & Limits" title="{{ $editingPromotion ? 'Edit Promotion Code' : 'Add New Promo Code' }}">
            <form method="POST" action="{{ $editingPromotion ? route('admin.promotions.update', $editingPromotion->id) : route('admin.promotions.store') }}" class="space-y-4">
                @csrf
                @if($editingPromotion)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.input label="Promo Code" name="promo_code" placeholder="e.g. MORNEWYEAR" value="{{ old('promo_code', $editingPromotion ? $editingPromotion->promo_code : '') }}" required />
                        <x-input-error :messages="$errors->get('promo_code')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.select label="Discount Type" name="discount_type" id="discount-type-select" onchange="toggleMaxDiscount(this.value)" required>
                            <option value="percentage" {{ old('discount_type', $editingPromotion ? $editingPromotion->discount_type : 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('discount_type', $editingPromotion ? $editingPromotion->discount_type : 'percentage') === 'fixed' ? 'selected' : '' }}>Fixed Amount (Rp)</option>
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('discount_type')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Discount Value" type="number" name="discount_value" placeholder="e.g. 10 for percentage, 20000 for fixed" value="{{ old('discount_value', $editingPromotion ? $editingPromotion->discount_value : 0) }}" required />
                        <x-input-error :messages="$errors->get('discount_value')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.input label="Minimum Transaction (Rp)" type="number" name="minimum_transaction" placeholder="e.g. 100000" value="{{ old('minimum_transaction', $editingPromotion ? $editingPromotion->minimum_transaction : 0) }}" required />
                        <x-input-error :messages="$errors->get('minimum_transaction')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Maximum Discount (Rp)" type="number" name="maximum_discount" id="max-discount-input" placeholder="e.g. 50000 (only for percentage, optional)" value="{{ old('maximum_discount', $editingPromotion ? $editingPromotion->maximum_discount : '') }}" />
                        <x-input-error :messages="$errors->get('maximum_discount')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Total Usage Limit" type="number" name="usage_limit" placeholder="e.g. 100 (optional)" value="{{ old('usage_limit', $editingPromotion ? $editingPromotion->usage_limit : '') }}" />
                        <x-input-error :messages="$errors->get('usage_limit')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-ui.select label="Status" name="is_active" required>
                        <option value="1" {{ old('is_active', $editingPromotion ? $editingPromotion->is_active : 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $editingPromotion ? $editingPromotion->is_active : 1) == 0 ? 'selected' : '' }}>Inactive</option>
                    </x-ui.select>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('admin.promotions') }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Cancel</a>
                    <x-ui.button variant="primary" type="submit">Save Promo</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @else
        <!-- List View Card -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('admin.promotions') }}" class="w-full md:max-w-xs">
                    <x-ui.input placeholder="Search promos by code..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                </form>
                <a href="?create=1" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    Add New Promo
                </a>
            </div>

            <div class="overflow-x-auto rounded-xl border border-stone-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">Promo Code</th>
                            <th class="py-3.5 px-4">Discount Rate</th>
                            <th class="py-3.5 px-4 text-right">Min. Spend</th>
                            <th class="py-3.5 px-4 text-right">Max. Discount</th>
                            <th class="py-3.5 px-4 text-center">Usage Statistics</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse($promotions as $promotion)
                            <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                <td class="py-3 px-4 font-mono font-bold text-stone-900 tracking-wider">{{ $promotion->promo_code }}</td>
                                <td class="py-3 px-4">
                                    <x-ui.badge variant="{{ $promotion->discount_type === 'percentage' ? 'success' : 'neutral' }}">
                                        {{ $promotion->discount_type === 'percentage' ? $promotion->discount_value . '%' : 'Rp ' . number_format($promotion->discount_value, 0, ',', '.') }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3 px-4 text-right font-mono">Rp {{ number_format($promotion->minimum_transaction, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-mono text-stone-600">{{ $promotion->maximum_discount ? 'Rp ' . number_format($promotion->maximum_discount, 0, ',', '.') : '-' }}</td>
                                <td class="py-3 px-4 text-center font-mono font-medium">
                                    {{ $promotion->usage_count }} / {{ $promotion->usage_limit ?? '∞' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="?edit={{ $promotion->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.promotions.delete', $promotion->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus promo ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-red-50 text-red-750 hover:bg-red-100 border border-red-200 rounded-lg text-xs font-bold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-stone-400">No promotions found matching query.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $promotions->links() }}
            </div>
        </div>
    @endif
</div>

<script>
    function toggleMaxDiscount(type) {
        const input = document.getElementById('max-discount-input');
        if (type === 'fixed') {
            input.disabled = true;
            input.value = '';
        } else {
            input.disabled = false;
        }
    }
    // Run on init
    toggleMaxDiscount(document.getElementById('discount-type-select').value);
</script>
@endsection
