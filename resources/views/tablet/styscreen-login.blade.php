@extends('layouts.tablet-blank')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-stone-50 p-4">
    <div class="w-full max-w-md p-8 glass-panel rounded-3xl bg-white border border-stone-200 shadow-xl space-y-6">
        <div class="text-center space-y-2">
            <span class="text-3xl font-black font-serif tracking-widest text-[#0A3D91]">MORE</span>
            <h2 class="text-xl font-bold text-stone-900 uppercase tracking-tight">STYSCREEN LOGIN</h2>
            <p class="text-xs text-stone-500">Masukkan email dan password admin outlet untuk membuka layar monitor kasir.</p>
        </div>

        @if(session()->has('error'))
            <x-ui.alert variant="danger">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('tablet.styscreen.login') }}" class="space-y-4">
            @csrf
            <div>
                <x-ui.input 
                    label="Email Admin" 
                    type="email" 
                    name="email"
                    placeholder="admin@morehairstudio.com" 
                    value="{{ old('email') }}"
                    required 
                />
            </div>

            <div>
                <x-ui.input 
                    label="Password" 
                    type="password" 
                    name="password"
                    placeholder="••••••••" 
                    required 
                />
            </div>

            <div class="pt-2">
                <x-ui.button 
                    variant="primary" 
                    type="submit" 
                    class="w-full h-[48px] rounded-lg shadow-sm font-bold uppercase tracking-wider"
                >
                    Masuk ke Styscreen
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
