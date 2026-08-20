<div>
    @slot('page_title')
        Stylists Management
    @endslot

    <div class="space-y-6">
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        @if($isEditing)
            <!-- Create/Edit Form Card -->
            <x-ui.card subtitle="Stylist Details" title="{{ $stylistId ? 'Edit Stylist' : 'Add New Stylist' }}">
                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <x-ui.input label="Stylist Name" placeholder="e.g. Raka Pratama" wire:model.live="name" :error="$errors->first('name')" />
                        </div>
                        <x-ui.input label="Slug" placeholder="e.g. raka-pratama" wire:model.defer="slug" :error="$errors->first('slug')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.select label="Assigned Outlet" wire:model.defer="outlet_id" :error="$errors->first('outlet_id')">
                            <option value="">-- Select Outlet --</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input label="Specialization" placeholder="e.g. Haircut & Styling" wire:model.defer="specialization" :error="$errors->first('specialization')" />
                        <x-ui.input label="WhatsApp Phone" placeholder="e.g. 62812345678" wire:model.defer="phone" :error="$errors->first('phone')" />
                        <x-ui.select label="Status" wire:model.defer="status" :error="$errors->first('status')">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </x-ui.select>
                    </div>

                    <div class="w-full">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Short Bio</label>
                        <textarea wire:model.defer="bio" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="A brief quote or details about their styling skills..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                        <x-ui.button variant="secondary" wire:click="cancel">Cancel</x-ui.button>
                        <x-ui.button variant="primary" type="submit">Save Stylist</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <!-- List View Card -->
            <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="w-full md:max-w-xs">
                        <x-ui.input placeholder="Search stylists by name..." wire:model.live="search" />
                    </div>
                    <x-ui.button variant="primary" wire:click="create">
                        Add New Stylist
                    </x-ui.button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Name</th>
                                <th class="py-3.5 px-4">Slug</th>
                                <th class="py-3.5 px-4">Assigned Outlet</th>
                                <th class="py-3.5 px-4">Specialization</th>
                                <th class="py-3.5 px-4">WhatsApp Phone</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse($stylists as $stylist)
                                <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                    <td class="py-3 px-4 font-bold text-stone-900">{{ $stylist->name }}</td>
                                    <td class="py-3 px-4 font-mono text-stone-500">{{ $stylist->slug }}</td>
                                    <td class="py-3 px-4 text-stone-600">{{ $stylist->outlet ? $stylist->outlet->name : '-' }}</td>
                                    <td class="py-3 px-4 font-medium">{{ $stylist->specialization ?? '-' }}</td>
                                    <td class="py-3 px-4 font-mono text-stone-500">{{ $stylist->phone ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <button wire:click="toggleStatus({{ $stylist->id }})" class="focus:outline-none">
                                            <x-ui.badge variant="{{ $stylist->status === 'active' ? 'success' : 'neutral' }}">
                                                {{ $stylist->status }}
                                            </x-ui.badge>
                                        </button>
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <x-ui.button variant="outline" size="sm" wire:click="edit({{ $stylist->id }})">
                                            Edit
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-stone-400">No stylists found matching query.</td>
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
</div>
