<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Outlets;
use App\Livewire\Admin\Services;
use App\Livewire\Admin\Stylists;
use App\Livewire\Admin\Customers;
use App\Livewire\Admin\Promotions;
use App\Livewire\Admin\Crm;
use App\Livewire\Admin\WhatsAppLogs;
use App\Livewire\Admin\Cms;
use App\Livewire\Admin\Seo;
use App\Livewire\Admin\Analytics;
use App\Livewire\Admin\Settings;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/outlets', Outlets::class)->name('outlets');
    Route::get('/services', Services::class)->name('services');
    Route::get('/stylists', Stylists::class)->name('stylists');
    Route::get('/customers', Customers::class)->name('customers');
    Route::get('/promotions', Promotions::class)->name('promotions');
    Route::get('/crm', Crm::class)->name('crm');
    Route::get('/whatsapp-logs', WhatsAppLogs::class)->name('whatsapp-logs');
    Route::get('/cms', Cms::class)->name('cms');
    Route::get('/seo', Seo::class)->name('seo');
    Route::get('/analytics', Analytics::class)->name('analytics');
    Route::get('/settings', Settings::class)->name('settings');
    Route::get('/pos', \App\Livewire\Admin\Pos::class)->name('pos');
    Route::get('/transactions', \App\Livewire\Admin\Transactions::class)->name('transactions');
    Route::get('/whatsapp', \App\Livewire\Admin\WhatsAppDashboard::class)->name('whatsapp');
    Route::get('/email', \App\Livewire\Admin\EmailDashboard::class)->name('email');
});
