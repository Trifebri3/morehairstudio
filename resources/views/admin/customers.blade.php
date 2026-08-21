@extends('layouts.admin')

@section('page_title')
    Customers CRM Management
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if($isCreating || $editingCustomer)
        <!-- Create/Edit Form Card -->
        <x-ui.card subtitle="Customer Profile Details" title="{{ $editingCustomer ? 'Edit Customer' : 'Add New Customer' }}">
            <form method="POST" action="{{ $editingCustomer ? route('admin.customers.update', $editingCustomer->id) : route('admin.customers.store') }}" class="space-y-4">
                @csrf
                @if($editingCustomer)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.input label="Customer Name" name="name" placeholder="e.g. Budi Hermawan" value="{{ old('name', $editingCustomer ? $editingCustomer->name : '') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Phone Number" name="phone" placeholder="e.g. 0812345678" value="{{ old('phone', $editingCustomer ? $editingCustomer->phone : '') }}" required />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.input label="Email Address" type="email" name="email" placeholder="e.g. budi@gmail.com" value="{{ old('email', $editingCustomer ? $editingCustomer->email : '') }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Birth Date" type="date" name="birth_date" value="{{ old('birth_date', $editingCustomer ? ($editingCustomer->birth_date ? $editingCustomer->birth_date->toDateString() : '') : '') }}" />
                        <x-input-error :messages="$errors->get('birth_date')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.select label="Gender" name="gender">
                            <option value="">-- Select Gender --</option>
                            <option value="male" {{ old('gender', $editingCustomer ? $editingCustomer->gender : '') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $editingCustomer ? $editingCustomer->gender : '') === 'female' ? 'selected' : '' }}>Female</option>
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.input label="Tags (dipisah koma)" name="tags" placeholder="e.g. VIP, Loyal, High Value" value="{{ old('tags', $editingCustomer ? (is_array($editingCustomer->tags) ? implode(', ', $editingCustomer->tags) : $editingCustomer->tags) : '') }}" />
                        <x-input-error :messages="$errors->get('tags')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.select label="Saluran Akuisisi" name="first_acquisition_source">
                            @foreach(['Website', 'WhatsApp', 'Instagram', 'TikTok', 'Google', 'Google Maps', 'Referral', 'Walk-in', 'Friend / Family', 'Offline Campaign', 'Event', 'Advertisement', 'Existing Customer', 'Other'] as $src)
                                <option value="{{ $src }}" {{ old('first_acquisition_source', $editingCustomer ? $editingCustomer->first_acquisition_source : 'Instagram') === $src ? 'selected' : '' }}>{{ $src }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('first_acquisition_source')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Alamat Rumah" name="address" placeholder="e.g. Jl. Merdeka No. 10" value="{{ old('address', $editingCustomer ? $editingCustomer->address : '') }}" />
                        <x-input-error :messages="$errors->get('address')" class="mt-1" />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-700">Catatan Internal CRM</label>
                    <textarea name="notes" placeholder="Masukkan catatan profil khusus..." class="w-full text-xs rounded-xl border-stone-200 bg-stone-50/50 p-3 h-20 text-stone-700 focus:border-[#0A3D91] transition">{{ old('notes', $editingCustomer ? $editingCustomer->notes : '') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('admin.customers') }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Cancel</a>
                    <x-ui.button variant="primary" type="submit">Save Profile</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @else
        <!-- List View Card -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('admin.customers') }}" class="w-full md:max-w-xs">
                    <x-ui.input placeholder="Search by name, phone or code..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                </form>
                <a href="?create=1" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    Add New Customer
                </a>
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
                                <td class="py-3 px-4 font-mono font-bold text-stone-550">{{ $customer->customer_code }}</td>
                                <td class="py-3 px-4 font-bold text-stone-900">{{ $customer->name }}</td>
                                <td class="py-3 px-4 font-mono">{{ $customer->phone }}</td>
                                <td class="py-3 px-4 text-stone-600">{{ $customer->email ?? '-' }}</td>
                                <td class="py-3 px-4 capitalize">{{ $customer->gender ?? '-' }}</td>
                                <td class="py-3 px-4 text-center font-bold font-mono text-[#0A3D91]">{{ $customer->bookings_count }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="?edit={{ $customer->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.customers.delete', $customer->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data customer ini?')" class="inline">
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
@endsection
