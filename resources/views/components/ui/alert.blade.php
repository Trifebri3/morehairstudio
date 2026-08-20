@props([
    'variant' => 'info', // success, danger, warning, info
    'title' => null
])

@php
    $variants = [
        'success' => 'bg-green-50 border-green-200 text-green-700',
        'danger' => 'bg-red-50 border-red-200 text-red-700',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
        'info' => 'bg-blue-50 border-blue-200 text-blue-700',
    ];

    $classes = 'p-4 rounded-lg border text-sm ' . $variants[$variant];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    @if($title)
        <h4 class="font-bold uppercase tracking-wider text-xs mb-1">{{ $title }}</h4>
    @endif
    <div>{{ $slot }}</div>
</div>
