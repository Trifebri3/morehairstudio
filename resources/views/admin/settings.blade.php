@extends('layouts.admin')

@section('page_title')
    Konfigurasi Sistem
@endsection

@section('content')
<div class="font-sans">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation Tabs -->
        <div class="w-full lg:w-64 flex flex-col space-y-2 shrink-0">
            <a href="?tab=general" class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'general' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg' : 'bg-white border-stone-200 text-stone-500 hover:text-stone-900' }}">
                <span>📁 General System</span>
            </a>
            <a href="?tab=whatsapp" class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'whatsapp' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg' : 'bg-white border-stone-200 text-stone-500 hover:text-stone-900' }}">
                <span>💬 WhatsApp API Gateway</span>
            </a>
            <a href="?tab=whatsapp_notifications" class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'whatsapp_notifications' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg' : 'bg-white border-stone-200 text-stone-500 hover:text-stone-900' }}">
                <span>🔔 Notification Toggles</span>
            </a>
            <a href="?tab=payment" class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'payment' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg' : 'bg-white border-stone-200 text-stone-500 hover:text-stone-900' }}">
                <span>💳 Midtrans Payment</span>
            </a>
        </div>

        <!-- Main Panel Content -->
        <div class="flex-grow">
            @if(session()->has('message'))
                <x-ui.alert variant="success" class="mb-6">
                    {{ session('message') }}
                </x-ui.alert>
            @endif

            <!-- Form Container -->
            <form method="POST" action="{{ route('admin.settings.update') }}?tab={{ $activeTab }}" class="glass-panel p-8 rounded-2xl bg-white border border-stone-200 shadow-sm flex flex-col space-y-6">
                @csrf
                
                <!-- Group Header -->
                <div class="border-b border-stone-150 pb-4">
                    <h2 class="text-base font-bold text-stone-900 uppercase tracking-wider">
                        @if($activeTab === 'general')
                            General System Config
                        @elseif($activeTab === 'whatsapp')
                            WhatsApp Gateway Settings
                        @elseif($activeTab === 'whatsapp_notifications')
                            Automated System Notifications
                        @elseif($activeTab === 'payment')
                            Midtrans Payment Integration
                        @endif
                    </h2>
                    <p class="text-xxs text-stone-400 font-mono uppercase tracking-wide mt-1">
                        Configure dynamic options that instantly drive behavior across domains.
                    </p>
                </div>

                <!-- Input Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($settingsMeta as $meta)
                        @php
                            $fieldName = str_replace('.', '_', $meta->key);
                            $isSecret = $meta->type === 'password';
                        @endphp
                        
                        <div class="w-full md:col-span-{{ $meta->type === 'textarea' || $meta->group === 'whatsapp_notifications' ? '2' : '1' }}">
                            @if($meta->type === 'boolean')
                                <!-- Checkbox Toggle Switch -->
                                <div class="flex items-start space-x-3 p-4 bg-stone-50 rounded-xl border border-stone-200 transition">
                                    <input 
                                        type="checkbox" 
                                        name="{{ $fieldName }}"
                                        id="{{ $meta->key }}" 
                                        value="1"
                                        {{ old($fieldName, $settingsData[$meta->key] ?? 'false') === 'true' ? 'checked' : '' }}
                                        class="h-4.5 w-4.5 rounded border-stone-300 bg-white text-[#0A3D91] focus:ring-[#0A3D91] mt-0.5">
                                    <div>
                                        <label for="{{ $meta->key }}" class="block text-xs font-bold uppercase tracking-wider text-stone-800 cursor-pointer select-none">
                                            {{ $meta->label }}
                                        </label>
                                        <p class="text-[10px] text-stone-500 mt-1 leading-relaxed">
                                            {{ $meta->description }}
                                        </p>
                                    </div>
                                </div>
                            @elseif($meta->type === 'select')
                                <!-- Select Input -->
                                <x-ui.select 
                                    label="{{ $meta->label }}" 
                                    name="{{ $fieldName }}"
                                    id="{{ $meta->key }}">
                                    @foreach((array)$meta->options as $valueOption => $labelOption)
                                        <option value="{{ $valueOption }}" {{ old($fieldName, $settingsData[$meta->key] ?? '') == $valueOption ? 'selected' : '' }}>{{ $labelOption }}</option>
                                    @endforeach
                                </x-ui.select>
                                <p class="text-[10px] text-stone-400 mt-1.5 px-1 leading-relaxed">
                                    {{ $meta->description }}
                                </p>
                            @elseif($meta->type === 'textarea')
                                <!-- Textarea Field -->
                                <div class="w-full">
                                    <label for="{{ $meta->key }}" class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">{{ $meta->label }}</label>
                                    <textarea 
                                        name="{{ $fieldName }}"
                                        id="{{ $meta->key }}" 
                                        rows="4" 
                                        class="w-full px-4 py-3 rounded-lg text-xs bg-white border border-stone-200 text-stone-900 placeholder-stone-400 focus:border-[#0A3D91] transition duration-300">{{ old($fieldName, $settingsData[$meta->key] ?? '') }}</textarea>
                                    <x-input-error :messages="$errors->get($fieldName)" class="mt-1" />
                                </div>
                                <p class="text-[10px] text-stone-400 mt-1.5 px-1 leading-relaxed">
                                    {{ $meta->description }}
                                </p>
                            @else
                                <!-- Standard text or masked password input -->
                                <div class="relative w-full">
                                    <x-ui.input 
                                        label="{{ $meta->label }}" 
                                        name="{{ $fieldName }}"
                                        id="{{ $meta->key }}" 
                                        type="{{ $isSecret ? 'password' : 'text' }}" 
                                        value="{{ old($fieldName, $settingsData[$meta->key] ?? '') }}" />
                                    <x-input-error :messages="$errors->get($fieldName)" class="mt-1" />
                                    
                                    @if($isSecret)
                                        <button 
                                            type="button" 
                                            onclick="toggleSecretVisibility('{{ $meta->key }}')"
                                            class="absolute right-3 bottom-2.5 text-stone-400 hover:text-stone-600 font-extrabold text-[9px] uppercase tracking-widest px-2 py-1 select-none">
                                            Show
                                        </button>
                                    @endif
                                </div>
                                <p class="text-[10px] text-stone-400 mt-1.5 px-1 leading-relaxed">
                                    {{ $meta->description }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Submit Button Area -->
                <div class="border-t border-stone-150 pt-6 flex justify-end">
                    <button 
                        type="submit" 
                        class="text-xs uppercase tracking-widest bg-[#0A3D91] hover:bg-blue-800 text-white px-6 py-3 rounded-xl transition font-extrabold shadow-sm">
                        Save Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleSecretVisibility(id) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }
</script>
@endsection
