@extends('layouts.admin')

@section('page_title')
    SEO Metadata Management
@endsection

@section('content')
<div class="space-y-6">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if($isCreating || $editingSeo)
        <!-- Create/Edit Form Card -->
        <x-ui.card subtitle="Page Metadata, OpenGraph & Schema" title="{{ $editingSeo ? 'Edit SEO Record' : 'Add New SEO Record' }}">
            <form method="POST" action="{{ $editingSeo ? route('admin.seo.update', $editingSeo->id) : route('admin.seo.store') }}" class="space-y-4">
                @csrf
                @if($editingSeo)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-ui.input label="Page Path (e.g. /, /about, /booking)" name="path" placeholder="e.g. /booking" value="{{ old('path', $editingSeo ? $editingSeo->path : '') }}" required />
                        <x-input-error :messages="$errors->get('path')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="Canonical URL (Optional)" name="canonical_url" placeholder="e.g. https://morehair.com/booking" value="{{ old('canonical_url', $editingSeo ? $editingSeo->canonical_url : '') }}" />
                        <x-input-error :messages="$errors->get('canonical_url')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.input label="Meta Title" name="meta_title" placeholder="e.g. Book Grooming Sesi Online | MORE Hair Studio" value="{{ old('meta_title', $editingSeo ? $editingSeo->meta_title : '') }}" required />
                        <x-input-error :messages="$errors->get('meta_title')" class="mt-1" />
                    </div>
                    <div>
                        <x-ui.input label="OpenGraph Title (Optional)" name="og_title" placeholder="e.g. Online Booking - MORE Hair Studio" value="{{ old('og_title', $editingSeo ? $editingSeo->og_title : '') }}" />
                        <x-input-error :messages="$errors->get('og_title')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Summarize page content in 150-160 characters..." required>{{ old('meta_description', $editingSeo ? $editingSeo->meta_description : '') }}</textarea>
                        <x-input-error :messages="$errors->get('meta_description')" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">OpenGraph Description (Optional)</label>
                        <textarea name="og_description" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder="Summarize Facebook/WhatsApp sharing copy...">{{ old('og_description', $editingSeo ? $editingSeo->og_description : '') }}</textarea>
                        <x-input-error :messages="$errors->get('og_description')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.input label="OpenGraph Image URL (Optional)" name="og_image" placeholder="e.g. https://morehair.com/images/og_main.jpg" value="{{ old('og_image', $editingSeo ? $editingSeo->og_image : '') }}" />
                        <x-input-error :messages="$errors->get('og_image')" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">JSON-LD Schema Script (Optional)</label>
                        <textarea name="schema" rows="3" class="w-full px-4 py-3 bg-[#fafaf9] border border-stone-200 text-stone-900 rounded-lg text-xs font-mono transition duration-300 focus:outline-none focus:border-[#0A3D91] placeholder-stone-450" placeholder='e.g. {"@@context": "https://schema.org", ...}'>{{ old('schema', $editingSeo ? $editingSeo->schema : '') }}</textarea>
                        <x-input-error :messages="$errors->get('schema')" class="mt-1" />
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('admin.seo') }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs transition border border-stone-200">Cancel</a>
                    <x-ui.button variant="primary" type="submit">Save SEO</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @else
        <!-- List View Card -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form method="GET" action="{{ route('admin.seo') }}" class="w-full md:max-w-xs">
                    <x-ui.input placeholder="Search by route path..." name="search" value="{{ $search }}" onchange="this.form.submit()" />
                </form>
                <a href="?create=1" class="inline-flex items-center justify-center px-4 py-2 bg-[#0A3D91] hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    Add SEO Record
                </a>
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
                                <td class="py-3 px-4 font-mono text-stone-550">{{ $record->canonical_url ?? '-' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="?edit={{ $record->id }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-stone-200 bg-white text-stone-700 hover:bg-stone-50 rounded-lg text-xs font-bold transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.seo.delete', $record->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data SEO ini?')" class="inline">
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
@endsection
