@extends('layouts.admin')

@section('page_title')
    Stylists Management
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if($isCreating || $editingStylist)
        <!-- Create/Edit Form Card -->
        <x-ui.card subtitle="Stylist Details" title="{{ $editingStylist ? 'Edit Stylist' : 'Add New Stylist' }}">
            <form method="POST" action="{{ $editingStylist ? route('admin.stylists.update', $editingStylist->id) : route('admin.stylists.store') }}" class="space-y-4">
                @csrf
                @if($editingStylist)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-ui.input label="Stylist Name" name="name" placeholder="e.g. Raka Pratama" value="{{ old('name', $editingStylist ? $editingStylist->name : '') }}" required oninput="updateSlug(this.value)" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Slug" name="slug" id="stylist-slug-input" placeholder="e.g. raka-pratama" value="{{ old('slug', $editingStylist ? $editingStylist->slug : '') }}" required />
                        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ui.select label="Assigned Outlet" name="outlet_id" required>
                            <option value="">-- Select Outlet --</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ old('outlet_id', $editingStylist ? $editingStylist->outlet_id : '') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('outlet_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Specialization" name="specialization" placeholder="e.g. Haircut & Styling" value="{{ old('specialization', $editingStylist ? $editingStylist->specialization : '') }}" />
                        <x-input-error :messages="$errors->get('specialization')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="WhatsApp Phone" name="phone" placeholder="e.g. 62812345678" value="{{ old('phone', $editingStylist ? $editingStylist->phone : '') }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.select label="Associated User Account" name="user_id">
                            <option value="">-- None --</option>
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}" {{ old('user_id', $editingStylist ? $editingStylist->user_id : '') == $usr->id ? 'selected' : '' }}>{{ $usr->name }} ({{ $usr->email }})</option>
                            @endforeach
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.select label="Status" name="status" required>
                            <option value="active" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="pending_active" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'pending_active' ? 'selected' : '' }}>Pending Active</option>
                            <option value="pending_inactive" {{ old('status', $editingStylist ? $editingStylist->status : 'active') === 'pending_inactive' ? 'selected' : '' }}>Pending Inactive</option>
                        </x-ui.select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Short Bio</label>
                    <textarea name="bio" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="A brief quote or details about their styling skills...">{{ old('bio', $editingStylist ? $editingStylist->bio : '') }}</textarea>
                    <x-input-error :messages="$errors->get('bio')" class="mt-1" />
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('admin.stylists') }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Cancel</a>
                    <x-ui.button variant="primary" type="submit">Save Stylist</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @else
        <!-- List View Card -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('admin.stylists') }}" class="w-full md:max-w-xs">
                    <x-ui.input placeholder="Search stylists by name..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                </form>
                <a href="?create=1" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    Add New Stylist
                </a>
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
                                <td class="py-3 px-4 font-mono text-stone-550">{{ $stylist->slug }}</td>
                                <td class="py-3 px-4 text-stone-600">{{ $stylist->outlet ? $stylist->outlet->name : '-' }}</td>
                                <td class="py-3 px-4 font-medium">{{ $stylist->specialization ?? '-' }}</td>
                                <td class="py-3 px-4 font-mono text-stone-550">{{ $stylist->phone ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-extrabold bg-stone-100 text-stone-600 border border-stone-200 uppercase tracking-wider">
                                        {{ $stylist->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="?edit={{ $stylist->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-stone-400">No stylists found matching query.</td>
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

<script>
    function updateSlug(val) {
        @if(!$editingStylist)
            const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            document.getElementById('stylist-slug-input').value = slug;
        @endif
    }
</script>
@endsection
