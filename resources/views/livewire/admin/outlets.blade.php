<div>
    @slot('page_title')
        Outlets Management
    @endslot

    <div class="space-y-6">
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        @if($isEditing)
            <!-- Create/Edit Form Card -->
            <x-ui.card subtitle="Outlet Details" title="{{ $outletId ? 'Edit Outlet' : 'Add New Outlet' }}">
                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="Outlet Name" placeholder="e.g. Jakarta SCBD Studio" wire:model.live="name" :error="$errors->first('name')" />
                        <x-ui.input label="Slug" placeholder="e.g. jakarta-scbd-studio" wire:model.defer="slug" :error="$errors->first('slug')" />
                    </div>

                    <x-ui.input label="Address" placeholder="e.g. Jl. Jenderal Sudirman No. 45" wire:model.defer="address" :error="$errors->first('address')" />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.input label="Phone Number" placeholder="e.g. 021-555123" wire:model.defer="phone" :error="$errors->first('phone')" />
                        <x-ui.input label="WhatsApp (For Client Alerts)" placeholder="e.g. 62812345678" wire:model.defer="whatsapp" :error="$errors->first('whatsapp')" />
                        <x-ui.select label="Status" wire:model.defer="status" :error="$errors->first('status')">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </x-ui.select>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                        <x-ui.button variant="secondary" wire:click="cancel">Cancel</x-ui.button>
                        <x-ui.button variant="primary" type="submit">Save Outlet</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <!-- List View Card -->
            <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="w-full md:max-w-xs">
                        <x-ui.input placeholder="Search outlets by name or address..." wire:model.live="search" />
                    </div>
                    <x-ui.button variant="primary" wire:click="create">
                        Add New Outlet
                    </x-ui.button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Name</th>
                                <th class="py-3.5 px-4">Slug</th>
                                <th class="py-3.5 px-4">Address</th>
                                <th class="py-3.5 px-4">WhatsApp</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse($outlets as $outlet)
                                <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                    <td class="py-3 px-4 font-bold text-stone-900">{{ $outlet->name }}</td>
                                    <td class="py-3 px-4 font-mono text-stone-500">{{ $outlet->slug }}</td>
                                    <td class="py-3 px-4 text-stone-600 truncate max-w-xs">{{ $outlet->address }}</td>
                                    <td class="py-3 px-4 font-mono">{{ $outlet->whatsapp ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <button wire:click="toggleStatus({{ $outlet->id }})" class="focus:outline-none">
                                            <x-ui.badge variant="{{ $outlet->status === 'active' ? 'success' : 'neutral' }}">
                                                {{ $outlet->status }}
                                            </x-ui.badge>
                                        </button>
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <x-ui.button variant="outline" size="sm" wire:click="edit({{ $outlet->id }})">
                                            Edit
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-stone-400">No outlets found matching query.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $outlets->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
