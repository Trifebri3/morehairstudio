@extends('layouts.admin')

@section('page_title')
    Manajemen Layanan & Kategori
@endsection

@section('content')
<div class="space-y-6 font-sans">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif
    @if(session()->has('error'))
        <x-ui.alert variant="danger">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <!-- Tab Selection Menu -->
    <div class="flex border-b border-stone-200">
        <a href="?tab=services" class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition {{ $activeTab === 'services' ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
            Daftar Jasa Layanan
        </a>
        <a href="?tab=categories" class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition {{ $activeTab === 'categories' ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
            Kategori Layanan (Categories)
        </a>
    </div>

    @if($activeTab === 'services')
        <!-- Services Panel -->
        @if($isCreating || $editingService)
            <!-- Create/Edit Service Form Card -->
            <x-ui.card subtitle="Service Details" title="{{ $editingService ? 'Edit Service' : 'Add New Service' }}">
                <form method="POST" action="{{ $editingService ? route('admin.services.update', $editingService->id) : route('admin.services.store') }}" class="space-y-4">
                    @csrf
                    @if($editingService)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <x-ui.input label="Service Name" name="name" placeholder="e.g. Signature Haircut" value="{{ old('name', $editingService ? $editingService->name : '') }}" required oninput="updateServiceSlug(this.value)" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="Slug" name="slug" id="service-slug-input" placeholder="e.g. signature-haircut" value="{{ old('slug', $editingService ? $editingService->slug : '') }}" required />
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-ui.select label="Service Category" name="service_category_id" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('service_category_id', $editingService ? $editingService->service_category_id : '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('service_category_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="Default Price (Rp)" type="number" name="default_price" placeholder="e.g. 150000" value="{{ old('default_price', $editingService ? $editingService->default_price : 0) }}" required />
                            <x-input-error :messages="$errors->get('default_price')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="Duration (Min)" type="number" name="default_duration" placeholder="e.g. 45" value="{{ old('default_duration', $editingService ? $editingService->default_duration : 30) }}" required />
                            <x-input-error :messages="$errors->get('default_duration')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.select label="Status" name="is_active">
                                <option value="1" {{ old('is_active', $editingService ? $editingService->is_active : 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $editingService ? $editingService->is_active : 1) == 0 ? 'selected' : '' }}>Inactive</option>
                            </x-ui.select>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
                        </div>
                    </div>

                    <div class="w-full">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Describe the treatment benefits and style output...">{{ old('description', $editingService ? $editingService->description : '') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                        <a href="{{ route('admin.services', ['tab' => 'services']) }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Cancel</a>
                        <x-ui.button variant="primary" type="submit">Save Service</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <!-- Services List Card -->
            <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <form method="GET" action="{{ route('admin.services') }}" class="w-full md:max-w-xs">
                        <input type="hidden" name="tab" value="services">
                        <x-ui.input placeholder="Search services by name..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                    </form>
                    <a href="?tab=services&create=1" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                        Add New Service
                    </a>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Name</th>
                                <th class="py-3.5 px-4">Category</th>
                                <th class="py-3.5 px-4">Duration</th>
                                <th class="py-3.5 px-4 text-right">Default Price</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse($services as $service)
                                <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                    <td class="py-3 px-4 font-bold text-stone-900">{{ $service->name }}</td>
                                    <td class="py-3 px-4 font-mono text-stone-500">{{ $service->category ? $service->category->name : '-' }}</td>
                                    <td class="py-3 px-4">{{ $service->default_duration }} Min</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-[#0A3D91]">Rp {{ number_format($service->default_price, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <form method="POST" action="{{ route('admin.services.toggle', $service->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="focus:outline-none">
                                                <x-ui.badge variant="{{ $service->is_active ? 'success' : 'neutral' }}">
                                                    {{ $service->is_active ? 'active' : 'inactive' }}
                                                </x-ui.badge>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="?tab=services&edit={{ $service->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-stone-400">No services found matching query.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $services->links() }}
                </div>
            </div>
        @endif
    @else
        <!-- Categories Panel -->
        @if($isCreatingCategory || $editingCategory)
            <!-- Create/Edit Category Form Card -->
            <x-ui.card subtitle="Category Details" title="{{ $editingCategory ? 'Edit Category' : 'Add New Category' }}">
                <form method="POST" action="{{ $editingCategory ? route('admin.categories.update', $editingCategory->id) : route('admin.categories.store') }}" class="space-y-4">
                    @csrf
                    @if($editingCategory)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.input label="Category Name" name="name" placeholder="e.g. Haircut" value="{{ old('name', $editingCategory ? $editingCategory->name : '') }}" required oninput="updateCategorySlug(this.value)" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-ui.input label="Slug" name="slug" id="category-slug-input" placeholder="e.g. haircut" value="{{ old('slug', $editingCategory ? $editingCategory->slug : '') }}" required />
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>
                    </div>

                    <div class="w-full">
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Describe the category definition...">{{ old('description', $editingCategory ? $editingCategory->description : '') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                        <a href="{{ route('admin.services', ['tab' => 'categories']) }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Cancel</a>
                        <x-ui.button variant="primary" type="submit">Save Category</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <!-- Categories List Card -->
            <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <form method="GET" action="{{ route('admin.services') }}" class="w-full md:max-w-xs">
                        <input type="hidden" name="tab" value="categories">
                        <x-ui.input placeholder="Search categories by name..." name="searchCategory" value="{{ $searchCategory }}" onchange="this.form.submit()" />
                    </form>
                    <a href="?tab=categories&create_category=1" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                        Add New Category
                    </a>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Name</th>
                                <th class="py-3.5 px-4">Slug</th>
                                <th class="py-3.5 px-4">Description</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse($paginatedCategories as $category)
                                <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                    <td class="py-3 px-4 font-bold text-stone-900">{{ $category->name }}</td>
                                    <td class="py-3 px-4 font-mono text-stone-550">{{ $category->slug }}</td>
                                    <td class="py-3 px-4 text-stone-600 truncate max-w-xs">{{ $category->description ?? '-' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="?tab=categories&edit_category={{ $category->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.categories.delete', $category->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Kategori tidak dapat dihapus jika memiliki layanan aktif terikat.')" class="inline">
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
                                    <td colspan="4" class="text-center py-8 text-stone-400">No categories found matching query.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $paginatedCategories->links() }}
                </div>
            </div>
        @endif
    @endif
</div>

<script>
    function updateServiceSlug(val) {
        @if(!$editingService)
            const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            document.getElementById('service-slug-input').value = slug;
        @endif
    }
    function updateCategorySlug(val) {
        @if(!$editingCategory)
            const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            document.getElementById('category-slug-input').value = slug;
        @endif
    }
</script>
@endsection
