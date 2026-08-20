<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Domains\System\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::subscribe(\App\Domains\System\Listeners\CommunicationListener::class);

        // Load settings from database dynamically
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::all();
                foreach ($settings as $setting) {
                    $value = $setting->value;
                    if ($setting->type === 'boolean') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    config([$setting->key => $value]);
                }
            }
        } catch (\Exception $e) {
            // Prevent failure when table does not exist during migrations or seeding
        }

        // Bridge standard application settings with the laravel-whatsapp-cloud package keys
        config([
            'whatsapp-cloud.access_token' => config('whatsapp.meta.token'),
            'whatsapp-cloud.phone_number_id' => config('whatsapp.meta.phone_number_id'),
            'whatsapp-cloud.webhook.verify_token' => config('whatsapp.meta.verify_token'),
            'whatsapp-cloud.webhook.app_secret' => env('WHATSAPP_APP_SECRET'),
        ]);

        // Register authorization gates
        \Illuminate\Support\Facades\Gate::define('customer.view', function ($user) {
            return in_array($user->role, ['super_admin', 'outlet_admin']);
        });
        \Illuminate\Support\Facades\Gate::define('customer.create', function ($user) {
            return in_array($user->role, ['super_admin', 'outlet_admin']);
        });
        \Illuminate\Support\Facades\Gate::define('customer.update', function ($user) {
            return in_array($user->role, ['super_admin', 'outlet_admin']);
        });
        \Illuminate\Support\Facades\Gate::define('customer.delete', function ($user) {
            return $user->role === 'super_admin';
        });
        \Illuminate\Support\Facades\Gate::define('customer.export', function ($user) {
            return in_array($user->role, ['super_admin', 'outlet_admin']);
        });

        \Illuminate\Support\Facades\Gate::define('pos.view', function ($user) {
            return in_array($user->role, ['super_admin', 'outlet_admin']);
        });
        \Illuminate\Support\Facades\Gate::define('pos.create', function ($user) {
            return in_array($user->role, ['super_admin', 'outlet_admin']);
        });
        \Illuminate\Support\Facades\Gate::define('pos.discount', function ($user) {
            return in_array($user->role, ['super_admin', 'outlet_admin']);
        });
        \Illuminate\Support\Facades\Gate::define('pos.refund', function ($user) {
            return $user->role === 'super_admin';
        });

        \Illuminate\Support\Facades\Gate::define('analytics.view', function ($user) {
            return $user->role === 'super_admin';
        });
    }
}
