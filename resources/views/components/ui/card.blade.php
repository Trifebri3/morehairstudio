@props([
    'title' => null,
    'subtitle' => null
])

<div {{ $attributes->merge(['class' => 'glass-panel p-6 rounded-2xl bg-white border border-stone-200 hover:border-blue-500/20 transition-all duration-300']) }}>
    @if($title || $subtitle)
        <div class="mb-5 border-b border-stone-100 pb-3">
            @if($title)
                <h3 class="text-base font-bold text-stone-900 font-sans tracking-wide uppercase">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="text-xxs text-stone-400 mt-1 uppercase tracking-widest font-mono">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div class="text-xs text-stone-600">
        {{ $slot }}
    </div>
</div>
