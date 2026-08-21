<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/outlets', [\App\Http\Controllers\Admin\OutletController::class, 'index'])->name('outlets');
    Route::post('/outlets', [\App\Http\Controllers\Admin\OutletController::class, 'store'])->name('outlets.store');
    Route::put('/outlets/{id}', [\App\Http\Controllers\Admin\OutletController::class, 'update'])->name('outlets.update');
    Route::post('/outlets/{id}/toggle', [\App\Http\Controllers\Admin\OutletController::class, 'toggleStatus'])->name('outlets.toggle');
    Route::get('/services', [\App\Http\Controllers\Admin\AdminPanelController::class, 'services'])->name('services');
    Route::post('/services', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeService'])->name('services.store');
    Route::put('/services/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateService'])->name('services.update');
    Route::post('/services/{id}/toggle', [\App\Http\Controllers\Admin\AdminPanelController::class, 'toggleServiceStatus'])->name('services.toggle');
    Route::post('/categories', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'deleteCategory'])->name('categories.delete');

    Route::get('/stylists', [\App\Http\Controllers\Admin\AdminPanelController::class, 'stylists'])->name('stylists');
    Route::post('/stylists', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeStylist'])->name('stylists.store');
    Route::put('/stylists/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateStylist'])->name('stylists.update');

    Route::get('/customers', [\App\Http\Controllers\Admin\AdminPanelController::class, 'customers'])->name('customers');
    Route::post('/customers', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeCustomer'])->name('customers.store');
    Route::put('/customers/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'deleteCustomer'])->name('customers.delete');

    Route::get('/promotions', [\App\Http\Controllers\Admin\AdminPanelController::class, 'promotions'])->name('promotions');
    Route::post('/promotions', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storePromotion'])->name('promotions.store');
    Route::put('/promotions/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updatePromotion'])->name('promotions.update');
    Route::delete('/promotions/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'deletePromotion'])->name('promotions.delete');

    Route::get('/crm', [\App\Http\Controllers\Admin\AdminPanelController::class, 'crm'])->name('crm');
    Route::get('/crm/export', [\App\Http\Controllers\Admin\AdminPanelController::class, 'exportCrm'])->name('crm.export');
    Route::get('/whatsapp-logs', [\App\Http\Controllers\Admin\AdminPanelController::class, 'whatsappLogs'])->name('whatsapp-logs');

    Route::get('/cms', [\App\Http\Controllers\Admin\AdminPanelController::class, 'cms'])->name('cms');
    Route::post('/cms', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateCms'])->name('cms.update');

    Route::get('/seo', [\App\Http\Controllers\Admin\AdminPanelController::class, 'seo'])->name('seo');
    Route::post('/seo', [\App\Http\Controllers\Admin\AdminPanelController::class, 'storeSeo'])->name('seo.store');
    Route::put('/seo/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateSeo'])->name('seo.update');
    Route::delete('/seo/{id}', [\App\Http\Controllers\Admin\AdminPanelController::class, 'deleteSeo'])->name('seo.delete');

    Route::get('/analytics', [\App\Http\Controllers\Admin\AdminPanelController::class, 'analytics'])->name('analytics');
    Route::get('/analytics/export', [\App\Http\Controllers\Admin\AdminPanelController::class, 'exportAnalytics'])->name('analytics.export');
    
    Route::get('/settings', [\App\Http\Controllers\Admin\AdminPanelController::class, 'settings'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\AdminPanelController::class, 'updateSettings'])->name('settings.update');

    Route::get('/pos', [\App\Http\Controllers\Admin\PosController::class, 'index'])->name('pos');
    Route::get('/pos/booking/{id}', [\App\Http\Controllers\Admin\PosController::class, 'getBooking'])->name('pos.booking');
    Route::post('/pos/checkout', [\App\Http\Controllers\Admin\PosController::class, 'checkout'])->name('pos.checkout');

    Route::get('/transactions', [\App\Http\Controllers\Admin\AdminPanelController::class, 'transactions'])->name('transactions');
    Route::post('/transactions/{id}/refund', [\App\Http\Controllers\Admin\AdminPanelController::class, 'refundTransaction'])->name('transactions.refund');
    Route::get('/whatsapp', [\App\Http\Controllers\Admin\WhatsAppController::class, 'index'])->name('whatsapp');
    Route::post('/whatsapp/toggle', [\App\Http\Controllers\Admin\WhatsAppController::class, 'toggleChannel'])->name('whatsapp.toggle');
    Route::post('/whatsapp/switch/{provider}', [\App\Http\Controllers\Admin\WhatsAppController::class, 'switchProvider'])->name('whatsapp.switch');
    Route::post('/whatsapp/config/cloud', [\App\Http\Controllers\Admin\WhatsAppController::class, 'saveCloudConfig'])->name('whatsapp.config.cloud');
    Route::post('/whatsapp/config/fonnte', [\App\Http\Controllers\Admin\WhatsAppController::class, 'saveFonnteConfig'])->name('whatsapp.config.fonnte');
    Route::post('/whatsapp/test/{provider}', [\App\Http\Controllers\Admin\WhatsAppController::class, 'testConnection'])->name('whatsapp.test');
    Route::post('/whatsapp/template', [\App\Http\Controllers\Admin\WhatsAppController::class, 'createTemplate'])->name('whatsapp.template.create');
    Route::delete('/whatsapp/template/{id}', [\App\Http\Controllers\Admin\WhatsAppController::class, 'deleteTemplate'])->name('whatsapp.template.delete');
    Route::post('/whatsapp/automation', [\App\Http\Controllers\Admin\WhatsAppController::class, 'createAutomation'])->name('whatsapp.automation.create');
    Route::delete('/whatsapp/automation/{id}', [\App\Http\Controllers\Admin\WhatsAppController::class, 'deleteAutomation'])->name('whatsapp.automation.delete');
    Route::post('/whatsapp/send/single', [\App\Http\Controllers\Admin\WhatsAppController::class, 'sendSingleMessage'])->name('whatsapp.send.single');
    Route::post('/whatsapp/send/bulk', [\App\Http\Controllers\Admin\WhatsAppController::class, 'sendBulkMessage'])->name('whatsapp.send.bulk');
    Route::post('/whatsapp/import', [\App\Http\Controllers\Admin\WhatsAppController::class, 'importContacts'])->name('whatsapp.import');

    Route::get('/email', [\App\Http\Controllers\Admin\EmailController::class, 'index'])->name('email');
    Route::post('/email/toggle', [\App\Http\Controllers\Admin\EmailController::class, 'toggleChannel'])->name('email.toggle');
    Route::post('/email/config', [\App\Http\Controllers\Admin\EmailController::class, 'saveConfig'])->name('email.config');
    Route::post('/email/template', [\App\Http\Controllers\Admin\EmailController::class, 'createTemplate'])->name('email.template');
    Route::delete('/email/template/{id}', [\App\Http\Controllers\Admin\EmailController::class, 'deleteTemplate'])->name('email.delete-template');
});
