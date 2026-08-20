<div>
    @slot('page_title')
        Promotions & Discounts Management
    @endslot

    <div class="space-y-6">
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        @if($isEditing)
            <!-- Create/Edit Form Card -->
            <x-ui.card subtitle="Discount Parameters & Limits" title="{{ $promotionId ? 'Edit Promotion Code' : 'Add New Promo Code' }}">
                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.input label="Promo Code" placeholder="e.g. MORNEWYEAR" wire:model.defer="promo_code" :error="$errors->first('promo_code')" />
                        <x-ui.select label="Discount Type" wire:model.live="discount_type" :error="$errors->first('discount_type')">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (Rp)</option>
                        </x-ui.select>
                        <x-ui.input label="Discount Value" type="number" placeholder="e.g. 10 for percentage, 20000 for fixed" wire:model.defer="discount_value" :error="$errors->first('discount_value')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.input label="Minimum Transaction (Rp)" type="number" placeholder="e.g. 100000" wire:model.defer="minimum_transaction" :error="$errors->first('minimum_transaction')" />
                        <x-ui.input label="Maximum Discount (Rp)" type="number" placeholder="e.g. 50000 (only for percentage, optional)" wire:model.defer="maximum_discount" :error="$errors->first('maximum_discount')" :disabled="$discount_type === 'fixed'" />
                        <x-ui.input label="Total Usage Limit" type="number" placeholder="e.g. 100 (optional)" wire:model.defer="usage_limit" :error="$errors->first('usage_limit')" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                        <x-ui.button variant="secondary" wire:click="cancel">Cancel</x-ui.button>
                        <x-ui.button variant="primary" type="submit">Save Promo</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <!-- List View Card -->
            <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="w-full md:max-w-xs">
                        <x-ui.input placeholder="Search promos by code..." wire:model.live="search" />
                    </div>
                    <x-ui.button variant="primary" wire:click="create">
                        Add New Promo
                    </x-ui.button>
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
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <x-ui.button variant="outline" size="sm" wire:click="edit({{ $promotion->id }})">
                                            Edit
                                        </x-ui.button>
                                        <x-ui.button variant="danger" size="sm" onclick="confirm('Apakah Anda yakin ingin menghapus data promo ini?') || event.stopImmediatePropagation()" wire:click="delete({{ $promotion->id }})">
                                            Hapus
                                        </x-ui.button>
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
</div>
