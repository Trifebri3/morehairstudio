<div>
    @slot('page_title')
        Konfigurasi Sistem
    @endslot

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation Tabs -->
        <div class="w-full lg:w-64 flex flex-col space-y-2 shrink-0">
            <button 
                type="button"
                wire:click="changeTab('general')" 
                class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'general' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg shadow-blue-900/10' : 'bg-stone-900/30 border-stone-850 text-stone-400 hover:text-white hover:bg-stone-850/50' }}">
                <span>📁 General System</span>
            </button>
            <button 
                type="button"
                wire:click="changeTab('whatsapp')" 
                class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'whatsapp' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg shadow-blue-900/10' : 'bg-stone-900/30 border-stone-850 text-stone-400 hover:text-white hover:bg-stone-850/50' }}">
                <span>💬 WhatsApp API Gateway</span>
            </button>
            <button 
                type="button"
                wire:click="changeTab('whatsapp_notifications')" 
                class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'whatsapp_notifications' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg shadow-blue-900/10' : 'bg-stone-900/30 border-stone-850 text-stone-400 hover:text-white hover:bg-stone-850/50' }}">
                <span>🔔 Notification Toggles</span>
            </button>
            <button 
                type="button"
                wire:click="changeTab('payment')" 
                class="flex items-center space-x-3 px-5 py-4 rounded-xl text-left transition duration-300 font-mono text-xs uppercase tracking-wider font-extrabold border {{ $activeTab === 'payment' ? 'bg-[#0A3D91] border-blue-500 text-white shadow-lg shadow-blue-900/10' : 'bg-stone-900/30 border-stone-850 text-stone-400 hover:text-white hover:bg-stone-850/50' }}">
                <span>💳 Midtrans Payment</span>
            </button>
        </div>

        <!-- Main Panel Content -->
        <div class="flex-grow">
            <!-- Success Message Notification -->
            @if($successMessage)
                <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-xs font-mono uppercase tracking-wider font-extrabold flex items-center space-x-2">
                    <span>✅ {{ $successMessage }}</span>
                </div>
            @endif

            <!-- Form Container -->
            <form wire:submit.prevent="save" class="glass-panel p-8 rounded-2xl border-stone-850 bg-stone-900/15 flex flex-col space-y-6">
                <!-- Group Header -->
                <div class="border-b border-stone-850 pb-4">
                    <h2 class="text-base font-bold font-serif gold-gradient-text uppercase tracking-wider">
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
                    <p class="text-xxs text-stone-500 font-mono uppercase tracking-wide mt-1">
                        Configure dynamic options that instantly drive behavior across domains.
                    </p>
                </div>

                <!-- Input Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($settingsMeta as $meta)
                        @php
                            $isSecret = $meta->type === 'password';
                            $showSecret = $isSecret && isset($showSecrets[$meta->key]) && $showSecrets[$meta->key];
                        @endphp
                        
                        <div class="w-full md:col-span-{{ $meta->type === 'textarea' || $meta->group === 'whatsapp_notifications' ? '2' : '1' }}">
                            @if($meta->type === 'boolean')
                                <!-- Checkbox Toggle Switch -->
                                <div class="flex items-start space-x-3 p-4 bg-stone-900/40 rounded-xl border border-stone-850/50 hover:border-stone-800 transition">
                                    <input 
                                        type="checkbox" 
                                        id="{{ $meta->key }}" 
                                        wire:model="settingsData.{{ $meta->key }}" 
                                        class="h-4.5 w-4.5 rounded border-stone-800 bg-stone-950 text-amber-500 focus:ring-offset-stone-950 focus:ring-amber-500 mt-0.5">
                                    <div>
                                        <label for="{{ $meta->key }}" class="block text-xs font-bold uppercase tracking-wider text-stone-300 cursor-pointer select-none">
                                            {{ $meta->label }}
                                        </label>
                                        <p class="text-xxs text-stone-500 mt-1 leading-relaxed">
                                            {{ $meta->description }}
                                        </p>
                                    </div>
                                </div>
                            @elseif($meta->type === 'select')
                                <!-- Select Input -->
                                <x-ui.select 
                                    label="{{ $meta->label }}" 
                                    id="{{ $meta->key }}" 
                                    wire:model="settingsData.{{ $meta->key }}" 
                                    error="{{ $errors->first($meta->key) }}">
                                    @foreach($meta->options as $valueOption => $labelOption)
                                        <option value="{{ $valueOption }}">{{ $labelOption }}</option>
                                    @endforeach
                                </x-ui.select>
                                <p class="text-xxs text-stone-500 mt-1.5 px-1 leading-relaxed">
                                    {{ $meta->description }}
                                </p>
                            @elseif($meta->type === 'textarea')
                                <!-- Textarea Field -->
                                <div class="w-full">
                                    <label for="{{ $meta->key }}" class="block text-[10px] uppercase tracking-widest text-stone-400 font-extrabold mb-2">{{ $meta->label }}</label>
                                    <textarea 
                                        id="{{ $meta->key }}" 
                                        wire:model="settingsData.{{ $meta->key }}" 
                                        rows="4" 
                                        class="w-full px-4 py-3 rounded-lg text-xs bg-stone-900 border border-stone-850 text-stone-100 placeholder-stone-600 focus:border-amber-500 transition duration-300"></textarea>
                                    @if($errors->has($meta->key))
                                        <span class="block mt-1.5 text-[10px] text-red-450 font-extrabold uppercase tracking-wider">{{ $errors->first($meta->key) }}</span>
                                    @endif
                                </div>
                                <p class="text-xxs text-stone-500 mt-1.5 px-1 leading-relaxed">
                                    {{ $meta->description }}
                                </p>
                            @else
                                <!-- Standard text or masked password input -->
                                <div class="relative w-full">
                                    <x-ui.input 
                                        label="{{ $meta->label }}" 
                                        id="{{ $meta->key }}" 
                                        type="{{ $isSecret && !$showSecret ? 'password' : 'text' }}" 
                                        wire:model="settingsData.{{ $meta->key }}" 
                                        error="{{ $errors->first($meta->key) }}" />
                                    
                                    @if($isSecret)
                                        <button 
                                            type="button" 
                                            wire:click="toggleSecretVisibility('{{ $meta->key }}')" 
                                            class="absolute right-3 bottom-2.5 text-stone-500 hover:text-stone-300 font-extrabold text-[10px] uppercase tracking-widest px-2 py-1 select-none">
                                            {{ $showSecret ? 'Hide' : 'Show' }}
                                        </button>
                                    @endif
                                </div>
                                <p class="text-xxs text-stone-500 mt-1.5 px-1 leading-relaxed">
                                    {{ $meta->description }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Submit Button Area -->
                <div class="border-t border-stone-850 pt-6 flex justify-end">
                    <button 
                        type="submit" 
                        class="text-xs uppercase tracking-widest bg-[#0A3D91] hover:bg-[#062e70] text-white px-6 py-3 rounded-lg transition font-extrabold shadow-sm active:scale-95 duration-200">
                        💾 Save Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
