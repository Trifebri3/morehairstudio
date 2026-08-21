@extends('layouts.admin')

@section('page_title')
    Konfigurasi Public (Bilingual Editor)
@endsection

@section('content')
<div class="space-y-8 font-sans">
    @if(session()->has('message'))
        <x-ui.alert variant="success">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="glass-panel p-8 rounded-2xl bg-white border border-stone-200 shadow-sm space-y-8">
        <div>
            <h3 class="text-lg font-bold text-stone-900 font-sans uppercase">Bilingual Content Editor</h3>
            <p class="text-xs text-stone-500 mt-1">Configure marketing titles and descriptions displayed on public marketing pages.</p>
        </div>

        <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-10">
            @csrf
            
            <!-- 1. Hero Section Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">01. Hero Section</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="Hero Tagline (ID)" name="hero_tagline_id" placeholder="e.g. Lebih Dari Sekadar Potong Rambut." value="{{ old('hero_tagline_id', $fields['hero_tagline_id']) }}" />
                    <x-ui.input label="Hero Tagline (EN)" name="hero_tagline_en" placeholder="e.g. More Than A Haircut." value="{{ old('hero_tagline_en', $fields['hero_tagline_en']) }}" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">Hero Description (ID)</label>
                        <textarea name="hero_description_id" class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3">{{ old('hero_description_id', $fields['hero_description_id']) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">Hero Description (EN)</label>
                        <textarea name="hero_description_en" class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3">{{ old('hero_description_en', $fields['hero_description_en']) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 2. About Section Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">02. About Section</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="About Tagline (ID)" name="about_tagline_id" placeholder="e.g. Mendefinisikan Ulang Pengalaman Potong Rambut Anda" value="{{ old('about_tagline_id', $fields['about_tagline_id']) }}" />
                    <x-ui.input label="About Tagline (EN)" name="about_tagline_en" placeholder="e.g. Re-Define Your Grooming Experience" value="{{ old('about_tagline_en', $fields['about_tagline_en']) }}" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 1 (ID)</label>
                        <textarea name="about_description_1_id" class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="4">{{ old('about_description_1_id', $fields['about_description_1_id']) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 1 (EN)</label>
                        <textarea name="about_description_1_en" class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="4">{{ old('about_description_1_en', $fields['about_description_1_en']) }}</textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 2 (ID)</label>
                        <textarea name="about_description_2_id" class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3">{{ old('about_description_2_id', $fields['about_description_2_id']) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 2 (EN)</label>
                        <textarea name="about_description_2_en" class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3">{{ old('about_description_2_en', $fields['about_description_2_en']) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 3. Why Section Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">03. Estetika Maksimal (Why MORE)</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="Title (ID)" name="why_title_id" placeholder="e.g. Estetika Maksimal" value="{{ old('why_title_id', $fields['why_title_id']) }}" />
                    <x-ui.input label="Title (EN)" name="why_title_en" placeholder="e.g. Maximum Aesthetics" value="{{ old('why_title_en', $fields['why_title_en']) }}" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="Subtitle (ID)" name="why_subtitle_id" placeholder="e.g. Komitmen kenyamanan..." value="{{ old('why_subtitle_id', $fields['why_subtitle_id']) }}" />
                    <x-ui.input label="Subtitle (EN)" name="why_subtitle_en" placeholder="e.g. Commitment to comfort..." value="{{ old('why_subtitle_en', $fields['why_subtitle_en']) }}" />
                </div>
            </div>

            <!-- 4. Payment Gateway Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">04. Integrasi Payment Gateway</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.select label="Status Payment Gateway (Midtrans)" name="payment_gateway_active">
                        <option value="false" {{ old('payment_gateway_active', $fields['payment_gateway_active']) === 'false' ? 'selected' : '' }}>Disabled / Nonaktif (Default: Bayar di Outlet)</option>
                        <option value="true" {{ old('payment_gateway_active', $fields['payment_gateway_active']) === 'true' ? 'selected' : '' }}>Enabled / Aktif (Tampilkan Pilihan Pembayaran Online)</option>
                    </x-ui.select>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('admin.cms') }}" class="inline-flex items-center justify-center px-4 py-2 bg-stone-150 hover:bg-stone-250 text-stone-700 font-bold rounded-xl text-xs transition border border-stone-200">Reset Fields</a>
                <x-ui.button variant="primary" size="md" type="submit">Save Translations</x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
