<div>
    @slot('page_title')
        Customers CRM Management
    @endslot

    <div class="space-y-6">
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        @if($isEditing)
            <!-- Create/Edit Form Card -->
            <x-ui.card subtitle="Customer Profile Details" title="{{ $customerId ? 'Edit Customer' : 'Add New Customer' }}">
                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="Customer Name" placeholder="e.g. Budi Hermawan" wire:model.defer="name" :error="$errors->first('name')" />
                        <x-ui.input label="Phone Number" placeholder="e.g. 0812345678" wire:model.defer="phone" :error="$errors->first('phone')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.input label="Email Address" type="email" placeholder="e.g. budi@gmail.com" wire:model.defer="email" :error="$errors->first('email')" />
                        <x-ui.input label="Birth Date" type="date" wire:model.defer="birth_date" :error="$errors->first('birth_date')" />
                        <x-ui.select label="Gender" wire:model.defer="gender" :error="$errors->first('gender')">
                            <option value="">-- Select Gender --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </x-ui.select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.input label="Tags (dipisah koma)" placeholder="e.g. VIP, Loyal, High Value" wire:model.defer="tags" :error="$errors->first('tags')" />
                        <x-ui.select label="Saluran Akuisisi" wire:model.defer="first_acquisition_source" :error="$errors->first('first_acquisition_source')">
                            @foreach(['Website', 'WhatsApp', 'Instagram', 'TikTok', 'Google', 'Google Maps', 'Referral', 'Walk-in', 'Friend / Family', 'Offline Campaign', 'Event', 'Advertisement', 'Existing Customer', 'Other'] as $src)
                                <option value="{{ $src }}">{{ $src }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input label="Alamat Rumah" placeholder="e.g. Jl. Merdeka No. 10" wire:model.defer="address" :error="$errors->first('address')" />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-stone-700">Catatan Internal CRM</label>
                        <textarea placeholder="Masukkan catatan profil khusus..." wire:model.defer="notes" class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 p-3 h-20 text-stone-700 focus:border-[#0A3D91] transition"></textarea>
                        @error('notes') <span class="text-xxs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                        <x-ui.button variant="secondary" wire:click="cancel">Cancel</x-ui.button>
                        <x-ui.button variant="primary" type="submit">Save Profile</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <!-- List View Card -->
            <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="w-full md:max-w-xs">
                        <x-ui.input placeholder="Search by name, phone or code..." wire:model.live="search" />
                    </div>
                    <x-ui.button variant="primary" wire:click="create">
                        Add New Customer
                    </x-ui.button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Code</th>
                                <th class="py-3.5 px-4">Name</th>
                                <th class="py-3.5 px-4">Phone</th>
                                <th class="py-3.5 px-4">Email</th>
                                <th class="py-3.5 px-4">Gender</th>
                                <th class="py-3.5 px-4 text-center">Bookings</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse($customers as $customer)
                                <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                    <td class="py-3 px-4 font-mono font-bold text-stone-500">{{ $customer->customer_code }}</td>
                                    <td class="py-3 px-4 font-bold text-stone-900">{{ $customer->name }}</td>
                                    <td class="py-3 px-4 font-mono">{{ $customer->phone }}</td>
                                    <td class="py-3 px-4 text-stone-600">{{ $customer->email ?? '-' }}</td>
                                    <td class="py-3 px-4 capitalize">{{ $customer->gender ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center font-bold font-mono text-[#0A3D91]">{{ $customer->bookings_count }}</td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <x-ui.button variant="outline" size="sm" wire:click="edit({{ $customer->id }})">
                                            Edit
                                        </x-ui.button>
                                        <x-ui.button variant="danger" size="sm" onclick="confirm('Apakah Anda yakin ingin menghapus data customer ini?') || event.stopImmediatePropagation()" wire:click="delete({{ $customer->id }})">
                                            Hapus
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-stone-400">No customers found matching query.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $customers->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
