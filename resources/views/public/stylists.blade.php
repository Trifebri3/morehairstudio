@extends('layouts.public')

@section('content')
<div class="py-24 max-w-5xl mx-auto px-4">
    <h1 class="text-4xl font-serif font-bold gold-gradient-text mb-6">Our Elite Stylists</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-12">
        @foreach($stylists as $stylist)
            <div class="glass-panel p-6 rounded-2xl flex flex-col justify-between">
                <div>
                    <span class="text-4xl mb-4 block">💇‍♂️</span>
                    <h3 class="text-xl font-bold font-serif text-stone-100 mb-1">{{ $stylist->name }}</h3>
                    <span class="text-xs text-amber-500 font-semibold uppercase tracking-wider block mb-3">{{ $stylist->specialization }}</span>
                    <p class="text-stone-400 text-xs leading-relaxed mb-6">{{ $stylist->bio }}</p>
                </div>
                <div class="border-t border-stone-850 pt-4 flex items-center justify-between text-xs text-stone-500">
                    <span>📍 {{ $stylist->outlet->name }}</span>
                    <span class="text-amber-500 font-bold">★ {{ number_format($stylist->rating, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
