@props([
    'variant' => 'primary', // primary, secondary, outline, danger
    'size' => 'md', // sm, md, lg
    'type' => 'button'
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-bold tracking-wide uppercase rounded-xl transition-all duration-300 select-none focus:outline-none';
    
    $variants = [
        'primary' => 'bg-[#c9512d] hover:bg-[#b74423] text-white shadow-sm hover:shadow-md hover:shadow-[#c9512d]/20 border border-transparent',
        'secondary' => 'bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200/60',
        'outline' => 'bg-transparent border border-[#c9512d]/40 hover:border-[#c9512d] hover:bg-[#faede7]/50 text-[#c9512d]',
        'danger' => 'bg-red-50 hover:bg-red-100 border border-red-200 text-red-650',
    ];

    $sizes = [
        'sm' => 'px-4 py-1.5 text-xs font-mono',
        'md' => 'px-6 py-2.5 text-xs tracking-wider',
        'lg' => 'px-8 py-3.5 text-sm tracking-widest',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp


<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
