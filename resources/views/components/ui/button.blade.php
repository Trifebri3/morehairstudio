@props([
    'variant' => 'primary', // primary, secondary, outline, danger
    'size' => 'md', // sm, md, lg
    'type' => 'button'
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-bold tracking-wide uppercase rounded-xl transition-all duration-300 select-none focus:outline-none';
    
    $variants = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-md hover:shadow-blue-500/10 border border-transparent',
        'secondary' => 'bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200/50',
        'outline' => 'bg-transparent border border-blue-500/30 hover:border-blue-500 hover:bg-blue-50 text-blue-600',
        'danger' => 'bg-red-50 hover:bg-red-100 border border-red-200 text-red-650',
    ];

    $sizes = [
        'sm' => 'px-4 py-1.5 text-xs',
        'md' => 'px-6 py-2.5 text-sm',
        'lg' => 'px-8 py-3.5 text-base',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
