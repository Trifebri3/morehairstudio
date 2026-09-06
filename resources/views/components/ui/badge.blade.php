@props([
    'variant' => 'neutral' // primary, success, warning, danger, neutral
])

@php
    $variants = [
        'primary' => 'bg-[#faede7] text-[#c9512d] border border-[#c9512d]/30 font-mono',
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 border border-amber-200',
        'danger' => 'bg-red-50 text-red-700 border border-red-200',
        'info' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'neutral' => 'bg-stone-100 text-stone-700 border border-stone-200',
    ];

    $classes = 'inline-flex items-center px-2.5 py-0.5 rounded text-xxs font-bold uppercase tracking-wider ' . ($variants[$variant] ?? $variants['neutral']);
@endphp


<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
