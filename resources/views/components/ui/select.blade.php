@props([
    'label' => null,
    'id' => null,
    'error' => null
])

@php
    $id = $id ?? 'select-' . uniqid();
    $selectClasses = 'bg-[#fafaf9] border border-stone-200 text-stone-900 focus:border-[#0A3D91]';
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $id }}" class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">{{ $label }}</label>
    @endif
    <select id="{{ $id }}" {{ $attributes->merge(['class' => 'w-full px-4 py-3 rounded-lg text-xs transition duration-300 ' . $selectClasses . ($error ? ' border-red-550 focus:border-red-550' : '')]) }}>
        {{ $slot }}
    </select>
    @if($error)
        <span class="block mt-1.5 text-[10px] text-red-450 font-extrabold uppercase tracking-wider">{{ $error }}</span>
    @endif
</div>
