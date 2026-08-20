@props([
    'variant' => 'neutral' // primary, success, warning, danger, neutral
])

@php
    $variants = [
        'primary' => 'bg-blue-50 text-blue-600 border border-blue-200',
        'success' => 'bg-green-50 text-green-700 border border-green-200',
        'warning' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'danger' => 'bg-red-50 text-red-700 border border-red-200',
        'neutral' => 'bg-stone-100 text-stone-700 border border-stone-200',
    ];

    $classes = 'inline-flex items-center px-2.5 py-0.5 rounded text-xxs font-bold uppercase tracking-wider ' . $variants[$variant];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
