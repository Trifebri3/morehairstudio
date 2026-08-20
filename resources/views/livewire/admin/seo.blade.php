<div>
    @slot('page_title')
        SEO Metadata Management
    @endslot

    <div class="space-y-6">
        @if(session()->has('message'))
            <x-ui.alert variant="success">
                {{ session('message') }}
            </x-ui.alert>
        @endif

        @if($isEditing)
            <!-- Create/Edit Form Card -->
            <x-ui.card subtitle="Page Metadata, OpenGraph & Schema" title="{{ $seoId ? 'Edit SEO Record' : 'Add New SEO Record' }}">
                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <x-ui.input label="Page Path (e.g. /, /about, /booking)" placeholder="e.g. /booking" wire:model.defer="path" :error="$errors->first('path')" />
                        </div>
                        <x-ui.input label="Canonical URL (Optional)" placeholder="e.g. https://morehair.com/booking" wire:model.defer="canonical_url" :error="$errors->first('canonical_url')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="Meta Title" placeholder="e.g. Book Grooming Sesi Online | MORE Hair Studio" wire:model.defer="meta_title" :error="$errors->first('meta_title')" />
                        <x-ui.input label="OpenGraph Title (Optional)" placeholder="e.g. Online Booking - MORE Hair Studio" wire:model.defer="og_title" :error="$errors->first('og_title')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Meta Description</label>
                            <textarea wire:model.defer="meta_description" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Summarize page content in 150-160 characters..."></textarea>
                            @error('meta_description') <span class="text-red-500 text-xxs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">OpenGraph Description (Optional)</label>
                            <textarea wire:model.defer="og_description" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Summarize Facebook/WhatsApp sharing copy..."></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="OpenGraph Image URL (Optional)" placeholder="e.g. https://morehair.com/images/og_main.jpg" wire:model.defer="og_image" :error="$errors->first('og_image')" />
                        <div>
                            <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">JSON-LD Schema Script (Optional)</label>
                            <textarea wire:model.defer="schema" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs font-mono transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder='e.g. {"@@context": "https://schema.org", ...}'></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                        <x-ui.button variant="secondary" wire:click="cancel">Cancel</x-ui.button>
                        <x-ui.button variant="primary" type="submit">Save SEO</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <!-- List View Card -->
            <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="w-full md:max-w-xs">
                        <x-ui.input placeholder="Search by route path..." wire:model.live="search" />
                    </div>
                    <x-ui.button variant="primary" wire:click="create">
                        Add SEO Record
                    </x-ui.button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Route Path</th>
                                <th class="py-3.5 px-4">Meta Title</th>
                                <th class="py-3.5 px-4">Meta Description</th>
                                <th class="py-3.5 px-4">Canonical Link</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse($seoRecords as $record)
                                <tr class="hover:bg-stone-50/50 transition text-stone-700">
                                    <td class="py-3 px-4 font-mono font-bold text-blue-600">{{ $record->path }}</td>
                                    <td class="py-3 px-4 font-bold text-stone-900">{{ $record->meta_title }}</td>
                                    <td class="py-3 px-4 text-stone-600 truncate max-w-xs">{{ $record->meta_description }}</td>
                                    <td class="py-3 px-4 font-mono text-stone-500">{{ $record->canonical_url ?? '-' }}</td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <x-ui.button variant="outline" size="sm" wire:click="edit({{ $record->id }})">
                                            Edit
                                        </x-ui.button>
                                        <x-ui.button variant="danger" size="sm" onclick="confirm('Apakah Anda yakin ingin menghapus data SEO ini?') || event.stopImmediatePropagation()" wire:click="delete({{ $record->id }})">
                                            Hapus
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-stone-400">No SEO records found matching query.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $seoRecords->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
