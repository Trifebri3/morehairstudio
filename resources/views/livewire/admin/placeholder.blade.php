<div>
    @slot('page_title')
        {{ $title }}
    @endslot
    
    <div class="glass-panel p-8 rounded-2xl text-center text-stone-500 py-16 bg-stone-900/30 border border-stone-850">
        <span class="text-4xl mb-4 block">⚙️</span>
        <h3 class="text-lg font-bold font-serif text-stone-300">{{ $title }} Panel</h3>
        <p class="text-xs text-stone-500 mt-2 max-w-sm mx-auto leading-relaxed">
            This module has been integrated into the backend architecture. Full configuration parameters can be managed directly in the Domain directories.
        </p>
    </div>
</div>
