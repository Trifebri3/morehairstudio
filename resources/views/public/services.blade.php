@extends('layouts.public')

@section('content')
<div class="py-24 max-w-5xl mx-auto px-4">
    <h1 class="text-4xl font-serif font-bold text-stone-900 mb-6">Our Menu of Treatments</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12">
        @foreach($services as $service)
            <div class="bg-white border border-stone-200 shadow-sm p-6 rounded-2xl flex justify-between items-start hover:shadow-md transition-shadow">
                <div>
                    <span class="text-xxs uppercase tracking-wider text-amber-600 font-semibold mb-1 block">{{ $service->category->name }}</span>
                    <h3 class="text-xl font-bold font-serif text-stone-900 mb-2">{{ $service->name }}</h3>
                    <p class="text-stone-600 text-xs leading-relaxed max-w-sm mb-4">{{ $service->description }}</p>
                    <span class="text-xxs font-mono text-stone-600">Duration: {{ $service->default_duration }} Min</span>
                </div>
                <div class="text-right">
                    <span class="text-amber-600 font-bold font-mono">Rp {{ number_format($service->default_price, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
