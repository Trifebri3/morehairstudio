@extends('layouts.admin')

@section('page_title')
    Outlets Management
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if($isCreating || $editingOutlet)
        <!-- Create/Edit Form Card -->
        <x-ui.card subtitle="Outlet Details" title="{{ $editingOutlet ? 'Edit Outlet' : 'Add New Outlet' }}">
            <form method="POST" action="{{ $editingOutlet ? route('admin.outlets.update', $editingOutlet->id) : route('admin.outlets.store') }}" class="space-y-4">
                @csrf
                @if($editingOutlet)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.input label="Outlet Name" name="name" id="outlet-name-input" placeholder="e.g. Jakarta SCBD Studio" value="{{ old('name', $editingOutlet ? $editingOutlet->name : '') }}" required oninput="updateSlug(this.value)" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Slug" name="slug" id="outlet-slug-input" placeholder="e.g. jakarta-scbd-studio" value="{{ old('slug', $editingOutlet ? $editingOutlet->slug : '') }}" required />
                        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-ui.input label="Address" name="address" placeholder="e.g. Jl. Jenderal Sudirman No. 45" value="{{ old('address', $editingOutlet ? $editingOutlet->address : '') }}" required />
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.input label="Phone Number" name="phone" placeholder="e.g. 021-555123" value="{{ old('phone', $editingOutlet ? $editingOutlet->phone : '') }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="WhatsApp (For Client Alerts)" name="whatsapp" placeholder="e.g. 62812345678" value="{{ old('whatsapp', $editingOutlet ? $editingOutlet->whatsapp : '') }}" />
                        <x-input-error :messages="$errors->get('whatsapp')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.select label="Status" name="status">
                            <option value="active" {{ old('status', $editingOutlet ? $editingOutlet->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $editingOutlet ? $editingOutlet->status : 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>

                <!-- Jam Operasional per Hari -->
                <div class="pt-4 border-t border-stone-100 space-y-3">
                    <div>
                        <h5 class="font-extrabold text-stone-900 text-xs uppercase tracking-wider font-display">Jam Operasional per Hari</h5>
                        <p class="text-stone-400 text-[10px]">Tentukan jam buka dan tutup atau hari libur operasional untuk masing-masing hari (misal Jumat buka 13:00 - 20:00).</p>
                    </div>

                    @php
                        $days = [
                            'monday'    => 'Senin',
                            'tuesday'   => 'Selasa',
                            'wednesday' => 'Rabu',
                            'thursday'  => 'Kamis',
                            'friday'    => 'Jumat',
                            'saturday'  => 'Sabtu',
                            'sunday'    => 'Minggu'
                        ];
                        $openingHours = ($editingOutlet && is_array($editingOutlet->opening_hours)) ? $editingOutlet->opening_hours : [];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach($days as $dayKey => $dayLabel)
                            @php
                                $dData = $openingHours[$dayKey] ?? ['is_open' => true, 'open' => '09:00', 'close' => '21:00'];
                                $isOpen = old("opening_hours.{$dayKey}.is_open", $dData['is_open'] ?? true);
                                $openTime = old("opening_hours.{$dayKey}.open", $dData['open'] ?? '09:00');
                                $closeTime = old("opening_hours.{$dayKey}.close", $dData['close'] ?? '21:00');
                            @endphp
                            <div class="p-3 bg-stone-50/70 border border-stone-200 rounded-2xl space-y-2">
                                <div class="flex items-center justify-between border-b border-stone-200/70 pb-2">
                                    <span class="font-bold text-xs text-stone-800">{{ $dayLabel }}</span>
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="hidden" name="opening_hours[{{ $dayKey }}][is_open]" value="0">
                                        <input type="checkbox" name="opening_hours[{{ $dayKey }}][is_open]" value="1" {{ $isOpen ? 'checked' : '' }} class="rounded border-stone-300 text-[#0A3D91] focus:ring-[#0A3D91]">
                                        <span class="text-xxs font-bold text-stone-600">Buka</span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xxs">
                                    <div>
                                        <label class="block text-stone-400 mb-0.5">Buka</label>
                                        <input type="time" name="opening_hours[{{ $dayKey }}][open]" value="{{ $openTime }}" class="w-full text-xs border-stone-200 focus:border-brand-500 rounded-lg p-1.5 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-stone-400 mb-0.5">Tutup</label>
                                        <input type="time" name="opening_hours[{{ $dayKey }}][close]" value="{{ $closeTime }}" class="w-full text-xs border-stone-200 focus:border-brand-500 rounded-lg p-1.5 font-mono">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('admin.outlets') }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Cancel</a>
                    <x-ui.button variant="primary" type="submit">Save Outlet</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @else
        <!-- List View Card -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('admin.outlets') }}" class="w-full md:max-w-xs">
                    <x-ui.input placeholder="Search outlets by name or address..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                </form>
                <a href="?create=1" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    Add New Outlet
                </a>
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
                                <td class="py-3 px-4 font-mono text-stone-550">{{ $outlet->slug }}</td>
                                <td class="py-3 px-4 text-stone-600 truncate max-w-xs">{{ $outlet->address }}</td>
                                <td class="py-3 px-4 font-mono">{{ $outlet->whatsapp ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <form method="POST" action="{{ route('admin.outlets.toggle', $outlet->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="focus:outline-none">
                                            <x-ui.badge variant="{{ $outlet->status === 'active' ? 'success' : 'neutral' }}">
                                                {{ $outlet->status }}
                                            </x-ui.badge>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="?edit={{ $outlet->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition">
                                        Edit
                                    </a>
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

<script>
    function updateSlug(val) {
        @if(!$editingOutlet)
            const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            document.getElementById('outlet-slug-input').value = slug;
        @endif
    }
</script>
@endsection
