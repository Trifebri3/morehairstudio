<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\System\Models\EmailConfiguration;
use App\Domains\System\Models\EmailTemplate;
use App\Domains\System\Models\EmailLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EmailDashboard extends Component
{
    // Global Channel Setting
    public $emailEnabled = true;

    // Configuration Fields
    public $host = '';
    public $port = 587;
    public $username = '';
    public $password = '';
    public $encryption = 'tls';
    public $fromAddress = '';
    public $fromName = '';
    public $isActive = true;

    // Tabs
    public $activeTab = 'overview'; // overview, settings, templates, logs

    // Template Creator
    public $tempName = '';
    public $tempSubject = '';
    public $tempBody = '';

    public function mount()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        // Pre-fill default template fields to prevent empty submission confusion
        $this->tempName = 'email_booking_confirm';
        $this->tempSubject = 'Konfirmasi Pemesanan - More Hair Studio';
        $this->tempBody = '<p>Halo {{customer_name}}, reservasi anda terkonfirmasi.</p>';

        // Load Global Email Setting
        $setting = DB::table('settings')->where('key', 'email_enabled')->first();
        if ($setting) {
            $this->emailEnabled = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        }

        // Load active SMTP configuration
        $config = EmailConfiguration::first();
        if ($config) {
            $this->host = $config->host ?? '';
            $this->port = $config->port ?? 587;
            $this->username = $config->username ?? '';
            $this->encryption = $config->encryption ?? 'tls';
            $this->fromAddress = $config->from_address ?? '';
            $this->fromName = $config->from_name ?? '';
            $this->isActive = (bool)$config->is_active;

            if (!empty($config->password)) {
                try {
                    $this->password = Crypt::decryptString($config->password);
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }
    }

    public function toggleChannel()
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'email_enabled'],
            ['value' => $this->emailEnabled ? 'true' : 'false', 'type' => 'boolean', 'updated_at' => now()]
        );
        session()->flash('message', 'Status Saluran Email berhasil diperbarui.');
    }

    public function saveConfig()
    {
        $this->validate([
            'host' => 'required',
            'port' => 'required|integer',
            'username' => 'required',
            'fromAddress' => 'required|email',
            'fromName' => 'required'
        ]);

        try {
            $encryptedPassword = !empty($this->password) ? Crypt::encryptString($this->password) : null;

            EmailConfiguration::updateOrCreate(
                ['id' => 1],
                [
                    'host' => $this->host,
                    'port' => intval($this->port),
                    'username' => $this->username,
                    'password' => $encryptedPassword,
                    'encryption' => $this->encryption,
                    'from_address' => $this->fromAddress,
                    'from_name' => $this->fromName,
                    'is_active' => $this->isActive
                ]
            );

            session()->flash('message', 'Konfigurasi SMTP Email berhasil disimpan.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function createTemplate()
    {
        $this->validate([
            'tempName' => 'required|unique:email_templates,name',
            'tempSubject' => 'required',
            'tempBody' => 'required'
        ]);

        EmailTemplate::create([
            'name' => $this->tempName,
            'subject' => $this->tempSubject,
            'body' => $this->tempBody,
            'variables' => [],
            'is_active' => true
        ]);

        $this->tempName = '';
        $this->tempSubject = '';
        $this->tempBody = '';
        session()->flash('message', 'Template Email baru berhasil ditambahkan.');
    }

    public function deleteTemplate($id)
    {
        EmailTemplate::destroy($id);
        session()->flash('message', 'Template Email berhasil dihapus.');
    }

    public function render()
    {
        // Stats
        $sentCount = EmailLog::where('status', 'SENT')->count();
        $failedCount = EmailLog::where('status', 'FAILED')->count();

        $templates = EmailTemplate::latest()->get();
        $logs = EmailLog::with('booking')->latest()->take(20)->get();

        return view('livewire.admin.email-dashboard', compact(
            'sentCount', 'failedCount', 'templates', 'logs'
        ))->layout('layouts.admin');
    }
}
