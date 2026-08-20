<div>
    @slot('page_title')
        Manajemen Layanan & Kategori
    @endslot

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
            <button wire:click="selectTab('services')" class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition {{ $activeTab === 'services' ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
                Daftar Jasa Layanan
            </button>
            <button wire:click="selectTab('categories')" class="px-5 py-3 text-xs font-bold uppercase tracking-wider border-b-2 transition {{ $activeTab === 'categories' ? 'border-[#0A3D91] text-[#0A3D91]' : 'border-transparent text-stone-500 hover:text-stone-850' }}">
                Kategori Layanan (Categories)
            </button>
        </div>

        @if($activeTab === 'services')
            <!-- Services Panel -->
            @if($isEditing)
                <!-- Create/Edit Service Form Card -->
                <x-ui.card subtitle="Service Details" title="{{ $serviceId ? 'Edit Service' : 'Add New Service' }}">
                    <form wire:submit.prevent="save" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <x-ui.input label="Service Name" placeholder="e.g. Signature Haircut" wire:model.live="name" :error="$errors->first('name')" />
                            </div>
                            <x-ui.input label="Slug" placeholder="e.g. signature-haircut" wire:model.defer="slug" :error="$errors->first('slug')" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <x-ui.select label="Service Category" wire:model.defer="service_category_id" :error="$errors->first('service_category_id')">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.input label="Default Price (Rp)" type="number" placeholder="e.g. 150000" wire:model.defer="default_price" :error="$errors->first('default_price')" />
                            <x-ui.input label="Duration (Min)" type="number" placeholder="e.g. 45" wire:model.defer="default_duration" :error="$errors->first('default_duration')" />
                            <x-ui.select label="Status" wire:model.defer="is_active" :error="$errors->first('is_active')">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </x-ui.select>
                        </div>

                        <div class="w-full">
                            <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Description</label>
                            <textarea wire:model.defer="description" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Describe the treatment benefits and style output..."></textarea>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                            <x-ui.button variant="secondary" wire:click="cancel">Cancel</x-ui.button>
                            <x-ui.button variant="primary" type="submit">Save Service</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @else
                <!-- Services List Card -->
                <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="w-full md:max-w-xs">
                            <x-ui.input placeholder="Search services by name..." wire:model.live="search" />
                        </div>
                        <x-ui.button variant="primary" wire:click="create">
                            Add New Service
                        </x-ui.button>
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
                                            <button wire:click="toggleActive({{ $service->id }})" class="focus:outline-none">
                                                <x-ui.badge variant="{{ $service->is_active ? 'success' : 'neutral' }}">
                                                    {{ $service->is_active ? 'active' : 'inactive' }}
                                                </x-ui.badge>
                                            </button>
                                        </td>
                                        <td class="py-3 px-4 text-right space-x-2">
                                            <x-ui.button variant="outline" size="sm" wire:click="edit({{ $service->id }})">
                                                Edit
                                            </x-ui.button>
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
            @if($isEditingCategory)
                <!-- Create/Edit Category Form Card -->
                <x-ui.card subtitle="Category Details" title="{{ $categoryId ? 'Edit Category' : 'Add New Category' }}">
                    <form wire:submit.prevent="saveCategory" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="Category Name" placeholder="e.g. Haircut" wire:model.live="categoryName" :error="$errors->first('categoryName')" />
                            <x-ui.input label="Slug" placeholder="e.g. haircut" wire:model.defer="categorySlug" :error="$errors->first('categorySlug')" />
                        </div>

                        <div class="w-full">
                            <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Description</label>
                            <textarea wire:model.defer="categoryDescription" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Describe the category definition..."></textarea>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                            <x-ui.button variant="secondary" wire:click="cancelCategory">Cancel</x-ui.button>
                            <x-ui.button variant="primary" type="submit">Save Category</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @else
                <!-- Categories List Card -->
                <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="w-full md:max-w-xs">
                            <x-ui.input placeholder="Search categories by name..." wire:model.live="searchCategory" />
                        </div>
                        <x-ui.button variant="primary" wire:click="createCategory">
                            Add New Category
                        </x-ui.button>
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
                                        <td class="py-3 px-4 font-mono text-stone-500">{{ $category->slug }}</td>
                                        <td class="py-3 px-4 text-stone-600 truncate max-w-xs">{{ $category->description ?? '-' }}</td>
                                        <td class="py-3 px-4 text-right space-x-2">
                                            <x-ui.button variant="outline" size="sm" wire:click="editCategory({{ $category->id }})">
                                                Edit
                                            </x-ui.button>
                                            <x-ui.button variant="danger" size="sm" onclick="confirm('Apakah Anda yakin ingin menghapus kategori ini? Kategori tidak dapat dihapus jika memiliki layanan aktif terikat.') || event.stopImmediatePropagation()" wire:click="deleteCategory({{ $category->id }})">
                                                Hapus
                                            </x-ui.button>
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
</div>
