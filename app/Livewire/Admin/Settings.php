<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\System\Models\Setting;
use Illuminate\Support\Facades\Schema;

class Settings extends Component
{
    // Active Tab: 'general', 'whatsapp', 'whatsapp_notifications', 'payment'
    public $activeTab = 'general';

    // Settings repository cache in memory
    public $settingsData = [];

    // Success alert message
    public $successMessage = null;

    // Password visibility toggles for credentials
    public $showSecrets = [
        'whatsapp.meta.token' => false,
        'whatsapp.fonnte.token' => false,
        'services.midtrans.server_key' => false,
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        if (Schema::hasTable('settings')) {
            $settings = Setting::all();
            foreach ($settings as $setting) {
                $this->settingsData[$setting->key] = $setting->value;
            }
        }
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
        $this->successMessage = null;
        $this->clearValidation();
    }

    public function toggleSecretVisibility($key)
    {
        if (isset($this->showSecrets[$key])) {
            $this->showSecrets[$key] = !$this->showSecrets[$key];
        }
    }

    public function save()
    {
        $this->successMessage = null;
        $this->clearValidation();

        // Perform validation manually to handle literal dots in settings array keys securely
        if ($this->activeTab === 'general') {
            if (empty($this->settingsData['app.name'])) {
                $this->addError('app.name', 'Nama aplikasi wajib diisi.');
            }
            if (empty($this->settingsData['app.url']) || !filter_var($this->settingsData['app.url'], FILTER_VALIDATE_URL)) {
                $this->addError('app.url', 'URL aplikasi harus berupa URL valid.');
            }
            if ($this->getErrorBag()->any()) {
                return;
            }
        } elseif ($this->activeTab === 'whatsapp') {
            $provider = $this->settingsData['whatsapp.provider'] ?? 'meta';
            if ($provider === 'meta') {
                if (empty($this->settingsData['whatsapp.meta.token'])) {
                    $this->addError('whatsapp.meta.token', 'Token Meta Cloud API wajib diisi.');
                }
                if (empty($this->settingsData['whatsapp.meta.phone_number_id'])) {
                    $this->addError('whatsapp.meta.phone_number_id', 'Phone Number ID wajib diisi.');
                }
                if (empty($this->settingsData['whatsapp.meta.version'])) {
                    $this->addError('whatsapp.meta.version', 'Versi API wajib diisi.');
                }
            } else {
                if (empty($this->settingsData['whatsapp.fonnte.token'])) {
                    $this->addError('whatsapp.fonnte.token', 'Token Fonnte wajib diisi.');
                }
            }
            if ($this->getErrorBag()->any()) {
                return;
            }
        } elseif ($this->activeTab === 'payment') {
            if (empty($this->settingsData['services.midtrans.server_key'])) {
                $this->addError('services.midtrans.server_key', 'Server Key Midtrans wajib diisi.');
            }
            if (empty($this->settingsData['services.midtrans.client_key'])) {
                $this->addError('services.midtrans.client_key', 'Client Key Midtrans wajib diisi.');
            }
            if ($this->getErrorBag()->any()) {
                return;
            }
        }

        // Save active tab's settings
        if (Schema::hasTable('settings')) {
            $settings = Setting::all();
            foreach ($settings as $setting) {
                $isTargetSetting = false;

                if ($this->activeTab === 'general' && $setting->group === 'general') {
                    $isTargetSetting = true;
                } elseif ($this->activeTab === 'whatsapp' && str_starts_with($setting->key, 'whatsapp.') && $setting->group !== 'whatsapp_notifications') {
                    $isTargetSetting = true;
                } elseif ($this->activeTab === 'whatsapp_notifications' && $setting->group === 'whatsapp_notifications') {
                    $isTargetSetting = true;
                } elseif ($this->activeTab === 'payment' && $setting->group === 'payment') {
                    $isTargetSetting = true;
                }

                if ($isTargetSetting && array_key_exists($setting->key, $this->settingsData)) {
                    $value = $this->settingsData[$setting->key];

                    // Handle boolean cast
                    if ($setting->type === 'boolean') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                    }

                    $setting->update(['value' => $value]);

                    // Update live configuration state
                    config([$setting->key => $setting->casted_value]);
                }
            }
        }

        $this->successMessage = 'Konfigurasi sistem berhasil diperbarui secara dinamis!';
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        // Fetch settings metadata grouped by their respective tab groups
        $settingsMeta = [];
        if (Schema::hasTable('settings')) {
            $query = Setting::query();
            
            if ($this->activeTab === 'whatsapp') {
                $query->where('key', 'like', 'whatsapp.%')->where('group', '!=', 'whatsapp_notifications');
            } elseif ($this->activeTab === 'whatsapp_notifications') {
                $query->where('group', 'whatsapp_notifications');
            } else {
                $query->where('group', $this->activeTab);
            }
            
            $settingsMeta = $query->orderBy('id', 'asc')->get();
        }

        return view('livewire.admin.settings', [
            'settingsMeta' => $settingsMeta
        ])->layout('layouts.admin');
    }
}
