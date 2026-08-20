<div class="space-y-8 font-sans">
    @slot('page_title')
        Konfigurasi Public (Bilingual Editor)
    @endslot

    <!-- Success Message Alert -->
    @if($successMessage)
        <x-ui.alert variant="success">
            {{ $successMessage }}
        </x-ui.alert>
    @endif

    <div class="glass-panel p-8 rounded-2xl border-stone-250 bg-white shadow-sm space-y-8">
        <div>
            <h3 class="text-lg font-bold text-stone-900 font-sans uppercase">Bilingual Content Editor</h3>
            <p class="text-xs text-stone-500 mt-1">Configure marketing titles and descriptions displayed on public marketing pages.</p>
        </div>

        <form wire:submit.prevent="save" class="space-y-10">
            <!-- 1. Hero Section Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">01. Hero Section</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="Hero Tagline (ID)" placeholder="e.g. Lebih Dari Sekadar Potong Rambut." wire:model.defer="hero_tagline_id" />
                    <x-ui.input label="Hero Tagline (EN)" placeholder="e.g. More Than A Haircut." wire:model.defer="hero_tagline_en" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">Hero Description (ID)</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3" wire:model.defer="hero_description_id"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">Hero Description (EN)</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3" wire:model.defer="hero_description_en"></textarea>
                    </div>
                </div>
            </div>

            <!-- 2. About Section Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">02. About Section</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="About Tagline (ID)" placeholder="e.g. Mendefinisikan Ulang Pengalaman Potong Rambut Anda" wire:model.defer="about_tagline_id" />
                    <x-ui.input label="About Tagline (EN)" placeholder="e.g. Re-Define Your Grooming Experience" wire:model.defer="about_tagline_en" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 1 (ID)</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="4" wire:model.defer="about_description_1_id"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 1 (EN)</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="4" wire:model.defer="about_description_1_en"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 2 (ID)</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3" wire:model.defer="about_description_2_id"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-stone-550 font-extrabold mb-2">About Description 2 (EN)</label>
                        <textarea class="w-full px-4 py-3 bg-white border border-stone-200 rounded-xl text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#0A3D91] transition duration-300" rows="3" wire:model.defer="about_description_2_en"></textarea>
                    </div>
                </div>
            </div>

            <!-- 3. Why Section Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">03. Estetika Maksimal (Why MORE)</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="Title (ID)" placeholder="e.g. Estetika Maksimal" wire:model.defer="why_title_id" />
                    <x-ui.input label="Title (EN)" placeholder="e.g. Maximum Aesthetics" wire:model.defer="why_title_en" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input label="Subtitle (ID)" placeholder="e.g. Komitmen kenyamanan..." wire:model.defer="why_subtitle_id" />
                    <x-ui.input label="Subtitle (EN)" placeholder="e.g. Commitment to comfort..." wire:model.defer="why_subtitle_en" />
                </div>
            </div>

            <!-- 4. Payment Gateway Configurations -->
            <div class="space-y-6 border-b border-stone-100 pb-8">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#0A3D91]">04. Integrasi Payment Gateway</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.select label="Status Payment Gateway (Midtrans)" wire:model.defer="payment_gateway_active">
                        <option value="false">Disabled / Nonaktif (Default: Bayar di Outlet)</option>
                        <option value="true">Enabled / Aktif (Tampilkan Pilihan Pembayaran Online)</option>
                    </x-ui.select>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex justify-end space-x-3 pt-4">
                <x-ui.button variant="secondary" size="md" type="button" wire:click="loadFields">Reset Fields</x-ui.button>
                <x-ui.button variant="primary" size="md" type="submit">Save Translations</x-ui.button>
            </div>
        </form>
    </div>
</div>
