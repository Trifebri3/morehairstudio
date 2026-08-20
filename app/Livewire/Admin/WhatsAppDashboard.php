<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Domains\WhatsApp\Models\WhatsAppConfiguration;
use App\Domains\WhatsApp\Models\WhatsAppTemplate;
use App\Domains\WhatsApp\Models\WhatsAppAutomation;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use App\Domains\WhatsApp\Providers\CloudApiWhatsAppProvider;
use App\Domains\WhatsApp\Providers\FonnteWhatsAppProvider;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Services\PhoneNormalizer;
use App\Domains\System\Services\CommunicationService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

use Livewire\WithPagination;

class WhatsAppDashboard extends Component
{
    use WithFileUploads;
    use WithPagination;
    // Global Settings
    public $whatsappEnabled = true;

    // Active Provider
    public $activeProvider = 'cloud_api';

    // Cloud API Settings
    public $cloudToken = '';
    public $cloudPhoneId = '';
    public $cloudVersion = 'v20.0';

    // Fonnte Settings
    public $fonnteToken = '';

    // Modals & Sub-tabs
    public $activeTab = 'overview'; // overview, settings, templates, automations, logs
    public $connectionStatus = 'Not Configured'; // Connected, Disconnected, Not Configured

    // Template Creator fields
    public $tempName = '';
    public $tempLanguage = 'id';
    public $tempBody = '';
    public $tempFile;

    // Automation Creator fields
    public $autoName = '';
    public $autoEvent = 'BOOKING_CONFIRMED';
    public $autoTemplate = '';
    public $autoDelay = 0;

    // Contact list & Broadcast properties
    public $searchContact = '';
    public $filterTag = '';
    public $selectedCustomerIds = [];
    
    // Bulk Send Modal
    public $isBulkModalOpen = false;
    public $bulkMessageText = '';
    public $bulkTemplateName = '';
    
    // Single Send Modal
    public $isSingleModalOpen = false;
    public $singleRecipientPhone = '';
    public $singleRecipientName = '';
    public $singleRecipientId = null;
    public $singleMessageText = '';
    public $singleTemplateName = '';

    // CSV Import
    public $csvFile;

    public function mount()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        // Pre-fill default template fields for a smoother UX
        $this->tempName = 'booking_confirmation';
        $this->tempLanguage = 'id';
        $this->tempBody = 'Halo {{customer_name}}, sesi reservasi Anda di {{outlet_name}} telah dikonfirmasi.';

        // Load Global WhatsApp setting
        $setting = DB::table('settings')->where('key', 'whatsapp_enabled')->first();
        if ($setting) {
            $this->whatsappEnabled = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        }

        // Load Provider Settings
        $cloudConfig = WhatsAppConfiguration::where('provider', 'cloud_api')->first();
        if ($cloudConfig && !empty($cloudConfig->config)) {
            try {
                $decrypted = json_decode(Crypt::decryptString($cloudConfig->config), true);
                $this->cloudToken = $decrypted['token'] ?? '';
                $this->cloudPhoneId = $decrypted['phone_number_id'] ?? '';
                $this->cloudVersion = $decrypted['version'] ?? 'v20.0';
            } catch (\Exception $e) {
                // Ignore decryption errors on empty/corrupt seed data
            }
        }

        $fonnteConfig = WhatsAppConfiguration::where('provider', 'fonnte')->first();
        if ($fonnteConfig && !empty($fonnteConfig->config)) {
            try {
                $decrypted = json_decode(Crypt::decryptString($fonnteConfig->config), true);
                $this->fonnteToken = $decrypted['token'] ?? '';
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // Detect current active provider
        $activeConfig = WhatsAppConfiguration::where('is_active', true)->first();
        if ($activeConfig) {
            $this->activeProvider = $activeConfig->provider;
            $this->connectionStatus = 'Connected';
        }
    }

    public function toggleChannel()
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'whatsapp_enabled'],
            ['value' => $this->whatsappEnabled ? 'true' : 'false', 'type' => 'boolean', 'updated_at' => now()]
        );
        session()->flash('message', 'Status WhatsApp berhasil diperbarui.');
    }

    public function switchProvider($providerName)
    {
        try {
            $decrypted = [];
            $targetConfig = WhatsAppConfiguration::where('provider', $providerName)->first();
            
            if ($providerName === 'cloud_api') {
                if (!$targetConfig || empty($targetConfig->config)) {
                    session()->flash('error', 'Kredensial untuk provider ini belum dikonfigurasi.');
                    return;
                }
                $decrypted = json_decode(Crypt::decryptString($targetConfig->config), true);
            } else {
                // Fonnte: allow env fallback
                $tokenToUse = '';
                if ($targetConfig && !empty($targetConfig->config)) {
                    $dbConfig = json_decode(Crypt::decryptString($targetConfig->config), true);
                    $tokenToUse = $dbConfig['token'] ?? '';
                }
                
                if (empty($tokenToUse)) {
                    $tokenToUse = env('FONNTE_TOKEN') ?: '';
                }

                if (empty($tokenToUse)) {
                    session()->flash('error', 'Kredensial untuk provider ini belum dikonfigurasi.');
                    return;
                }

                $decrypted = [
                    'token' => $tokenToUse,
                    'mock' => false
                ];
            }

            // Validate target configuration before switching (Scenario 2 rule: don't break if invalid)
            $isValid = false;
            if ($providerName === 'cloud_api') {
                $provider = new CloudApiWhatsAppProvider($decrypted);
                $isValid = $provider->validateConfiguration($decrypted);
            } else {
                $provider = new FonnteWhatsAppProvider($decrypted);
                $isValid = $provider->validateConfiguration($decrypted);
            }

            if (!$isValid) {
                session()->flash('error', 'Gagal berpindah provider: Kredensial tidak valid saat diuji koneksi.');
                return;
            }

            // Perform switching inside transaction
            DB::transaction(function () use ($providerName) {
                WhatsAppConfiguration::where('is_active', true)->update(['is_active' => false]);
                WhatsAppConfiguration::updateOrCreate(
                    ['provider' => $providerName],
                    ['is_active' => true]
                );
            });

            $this->activeProvider = $providerName;
            $this->connectionStatus = 'Connected';
            session()->flash('message', 'Provider WhatsApp berhasil diganti ke ' . strtoupper($providerName));
        } catch (\Exception $e) {
            session()->flash('error', 'Error saat beralih: ' . $e->getMessage());
        }
    }

    public function saveCloudConfig()
    {
        $this->validate([
            'cloudToken' => 'required',
            'cloudPhoneId' => 'required',
            'cloudVersion' => 'required'
        ]);

        try {
            $configData = [
                'token' => $this->cloudToken,
                'phone_number_id' => $this->cloudPhoneId,
                'version' => $this->cloudVersion,
                'mock' => false
            ];

            // Encrypt and save
            $encrypted = Crypt::encryptString(json_encode($configData));

            WhatsAppConfiguration::updateOrCreate(
                ['provider' => 'cloud_api'],
                ['config' => $encrypted]
            );

            session()->flash('message', 'Konfigurasi WhatsApp Cloud API berhasil disimpan.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function saveFonnteConfig()
    {
        $this->validate([
            'fonnteToken' => 'required'
        ]);

        try {
            $configData = [
                'token' => $this->fonnteToken,
                'mock' => false
            ];

            $encrypted = Crypt::encryptString(json_encode($configData));

            WhatsAppConfiguration::updateOrCreate(
                ['provider' => 'fonnte'],
                ['config' => $encrypted]
            );

            session()->flash('message', 'Konfigurasi Fonnte berhasil disimpan.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function testConnection($providerName)
    {
        try {
            $decrypted = [];
            
            if ($providerName === 'cloud_api') {
                $tokenToTest = $this->cloudToken;
                $phoneIdToTest = $this->cloudPhoneId;
                $versionToTest = $this->cloudVersion;

                // Fallback to database config if inputs are empty
                if (empty($tokenToTest)) {
                    $configRecord = WhatsAppConfiguration::where('provider', 'cloud_api')->first();
                    if ($configRecord && !empty($configRecord->config)) {
                        $dbConfig = json_decode(Crypt::decryptString($configRecord->config), true);
                        $tokenToTest = $dbConfig['token'] ?? '';
                        $phoneIdToTest = $dbConfig['phone_number_id'] ?? '';
                        $versionToTest = $dbConfig['version'] ?? '';
                    }
                }

                $decrypted = [
                    'token' => $tokenToTest,
                    'phone_number_id' => $phoneIdToTest,
                    'version' => $versionToTest,
                    'mock' => false
                ];
            } else {
                $tokenToTest = $this->fonnteToken;
                
                // Fallback to env first, then database
                if (empty($tokenToTest)) {
                    $tokenToTest = env('FONNTE_TOKEN') ?: '';
                }
                
                if (empty($tokenToTest)) {
                    $configRecord = WhatsAppConfiguration::where('provider', 'fonnte')->first();
                    if ($configRecord && !empty($configRecord->config)) {
                        $dbConfig = json_decode(Crypt::decryptString($configRecord->config), true);
                        $tokenToTest = $dbConfig['token'] ?? '';
                    }
                }

                $decrypted = [
                    'token' => $tokenToTest,
                    'mock' => false
                ];
            }

            if (empty($decrypted['token'])) {
                $this->connectionStatus = 'Disconnected';
                session()->flash('error', 'Koneksi Gagal: Kredensial kosong.');
                return;
            }

            $isValid = false;

            if ($providerName === 'cloud_api') {
                $provider = new CloudApiWhatsAppProvider($decrypted);
                $isValid = $provider->validateConfiguration($decrypted);
            } else {
                $provider = new FonnteWhatsAppProvider($decrypted);
                $isValid = $provider->validateConfiguration($decrypted);
            }

            if ($isValid) {
                $this->connectionStatus = 'Connected';
                session()->flash('message', 'Tes Koneksi Sukses! Status: Terhubung.');
            } else {
                $this->connectionStatus = 'Error';
                session()->flash('error', 'Tes Koneksi Gagal. Periksa token Anda.');
            }
        } catch (\Exception $e) {
            $this->connectionStatus = 'Error';
            session()->flash('error', 'Kesalahan tes: ' . $e->getMessage());
        }
    }

    public function createTemplate()
    {
        $this->validate([
            'tempName' => 'required|unique:whatsapp_templates,template_name',
            'tempBody' => 'required',
            'tempFile' => 'nullable|file|max:10240'
        ]);

        $filePath = null;
        if ($this->tempFile) {
            $path = $this->tempFile->store('whatsapp/templates', 'public');
            $filePath = '/storage/' . $path;
        }

        WhatsAppTemplate::create([
            'template_name' => $this->tempName,
            'language' => $this->tempLanguage,
            'body' => $this->tempBody,
            'variables' => [],
            'is_active' => true,
            'file_path' => $filePath
        ]);

        $this->tempName = '';
        $this->tempBody = '';
        $this->tempFile = null;
        session()->flash('message', 'Template baru berhasil ditambahkan.');
    }

    public function deleteTemplate($id)
    {
        WhatsAppTemplate::destroy($id);
        session()->flash('message', 'Template berhasil dihapus.');
    }

    public function createAutomation()
    {
        $this->validate([
            'autoName' => 'required',
            'autoTemplate' => 'required'
        ]);

        WhatsAppAutomation::create([
            'name' => $this->autoName,
            'event_type' => $this->autoEvent,
            'template_name' => $this->autoTemplate,
            'delay_minutes' => intval($this->autoDelay),
            'is_active' => true
        ]);

        $this->autoName = '';
        $this->autoDelay = 0;
        session()->flash('message', 'Aturan Otomasi berhasil dibuat.');
    }

    public function deleteAutomation($id)
    {
        WhatsAppAutomation::destroy($id);
        session()->flash('message', 'Aturan Otomasi berhasil dihapus.');
    }

    // Single Send Modal triggers
    public function openSingleModal($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $this->singleRecipientId = $customer->id;
        $this->singleRecipientName = $customer->name;
        $this->singleRecipientPhone = $customer->phone;
        $this->singleMessageText = '';
        $this->singleTemplateName = '';
        $this->isSingleModalOpen = true;
    }

    public function updatedSingleTemplateName($value)
    {
        if (!empty($value)) {
            $template = WhatsAppTemplate::where('template_name', $value)->first();
            if ($template) {
                // Replace basic variables
                $text = $template->body;
                $text = str_replace('{{customer_name}}', $this->singleRecipientName, $text);
                $text = str_replace('{{booking_code}}', 'DIRECT', $text);
                $this->singleMessageText = $text;
            }
        } else {
            $this->singleMessageText = '';
        }
    }

    public function sendSingleMessage()
    {
        $this->validate([
            'singleRecipientPhone' => 'required',
            'singleMessageText' => 'required'
        ]);

        try {
            $recipient = PhoneNormalizer::normalize($this->singleRecipientPhone);
            CommunicationService::sendWhatsApp($recipient, $this->singleMessageText);

            $this->isSingleModalOpen = false;
            $this->singleMessageText = '';
            $this->singleTemplateName = '';
            session()->flash('message', 'Pesan WhatsApp berhasil dikirim ke ' . $this->singleRecipientName);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim pesan: ' . $e->getMessage());
        }
    }

    // Bulk Send Modal triggers
    public function openBulkModal()
    {
        if (empty($this->selectedCustomerIds)) {
            session()->flash('error', 'Pilih minimal satu kontak untuk mengirim pesan massal.');
            return;
        }
        $this->bulkMessageText = '';
        $this->bulkTemplateName = '';
        $this->isBulkModalOpen = true;
    }

    public function updatedBulkTemplateName($value)
    {
        if (!empty($value)) {
            $template = WhatsAppTemplate::where('template_name', $value)->first();
            if ($template) {
                $this->bulkMessageText = $template->body;
            }
        } else {
            $this->bulkMessageText = '';
        }
    }

    public function sendBulkMessage()
    {
        $this->validate([
            'bulkMessageText' => 'required'
        ]);

        if (empty($this->selectedCustomerIds)) {
            session()->flash('error', 'Pilih minimal satu kontak.');
            return;
        }

        try {
            $customers = Customer::whereIn('id', $this->selectedCustomerIds)->get();
            $count = 0;

            foreach ($customers as $customer) {
                $recipient = PhoneNormalizer::normalize($customer->phone);
                
                // Resolve template variables per customer dynamically
                $resolvedBody = $this->bulkMessageText;
                $resolvedBody = str_replace('{{customer_name}}', $customer->name, $resolvedBody);
                $resolvedBody = str_replace('{{booking_code}}', 'PROMO', $resolvedBody);

                CommunicationService::sendWhatsApp($recipient, $resolvedBody);
                $count++;
            }

            $this->isBulkModalOpen = false;
            $this->selectedCustomerIds = [];
            $this->bulkMessageText = '';
            $this->bulkTemplateName = '';
            session()->flash('message', "Pesan massal berhasil dikirim ke {$count} kontak.");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim pesan massal: ' . $e->getMessage());
        }
    }

    // CSV Import Action
    public function importContacts()
    {
        $this->validate([
            'csvFile' => 'required|file|max:5120'
        ]);

        try {
            $path = $this->csvFile->getRealPath();
            $file = fopen($path, 'r');
            
            // Detect separator (comma or semicolon)
            $headerLine = fgets($file);
            $separator = ',';
            if (str_contains($headerLine, ';')) {
                $separator = ';';
            }
            
            // Rewind
            rewind($file);

            // Read headers
            $headers = fgetcsv($file, 1000, $separator);
            if (!$headers) {
                session()->flash('error', 'File CSV kosong.');
                fclose($file);
                return;
            }

            // Clean headers
            $headers = array_map(function($h) {
                return strtolower(trim(str_replace(['"', "'"], '', $h)));
            }, $headers);

            $nameIdx = array_search('name', $headers);
            $phoneIdx = array_search('phone', $headers);
            $emailIdx = array_search('email', $headers);

            if ($nameIdx === false || $phoneIdx === false) {
                session()->flash('error', 'Format CSV salah. Harus memiliki header kolom "name" dan "phone".');
                fclose($file);
                return;
            }

            $imported = 0;
            while (($row = fgetcsv($file, 1000, $separator)) !== false) {
                if (count($row) < 2) continue;

                $name = trim($row[$nameIdx] ?? '');
                $rawPhone = trim($row[$phoneIdx] ?? '');
                $email = $emailIdx !== false ? trim($row[$emailIdx] ?? '') : null;

                if (empty($name) || empty($rawPhone)) continue;

                try {
                    $phone = PhoneNormalizer::normalize($rawPhone);
                } catch (\Exception $e) {
                    continue; // Skip invalid phone numbers
                }

                // Check CRM duplicate
                $customer = Customer::where('phone', $phone)->first();
                if (!$customer) {
                    $total = Customer::count() + 1;
                    $custCode = 'CUST-' . str_pad((string)$total, 5, '0', STR_PAD_LEFT);
                    
                    Customer::create([
                        'customer_code' => $custCode,
                        'name' => $name,
                        'phone' => $phone,
                        'whatsapp_phone' => $phone,
                        'email' => !empty($email) ? $email : null,
                        'whatsapp_marketing_opt_in' => true,
                        'email_marketing_opt_in' => true
                    ]);
                    $imported++;
                } else {
                    // Update if existing but missing email
                    if ($email && !$customer->email) {
                        $customer->update(['email' => $email]);
                    }
                }
            }

            fclose($file);
            $this->csvFile = null;
            session()->flash('message', "Berhasil mengimpor {$imported} kontak baru ke CRM.");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimpor file CSV: ' . $e->getMessage());
        }
    }

    public function selectAllContacts($idsJson)
    {
        $this->selectedCustomerIds = json_decode($idsJson, true) ?: [];
    }

    public function deselectAllContacts()
    {
        $this->selectedCustomerIds = [];
    }

    public function render()
    {
        // Overview count statistics
        $sentToday = WhatsAppMessage::whereDate('created_at', today())->where('status', 'SENT')->count();
        $failedToday = WhatsAppMessage::whereDate('created_at', today())->where('status', 'FAILED')->count();
        $deliveredToday = WhatsAppMessage::whereDate('created_at', today())->where('status', 'DELIVERED')->count();
        $readToday = WhatsAppMessage::whereDate('created_at', today())->where('status', 'READ')->count();
        
        $campaignsActive = DB::table('whatsapp_campaigns')->where('status', 'PROCESSING')->count();
        $automationsActive = WhatsAppAutomation::where('is_active', true)->count();
        
        // Data arrays
        $templates = WhatsAppTemplate::latest()->get();
        $automations = WhatsAppAutomation::latest()->get();
        $logs = WhatsAppMessage::with('booking')->latest()->take(20)->get();

        // Query & filter contacts (CRM)
        $query = Customer::query()->withCount('bookings');
        if (!empty($this->searchContact)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchContact . '%')
                  ->orWhere('phone', 'like', '%' . $this->searchContact . '%');
            });
        }
        if (!empty($this->filterTag)) {
            $query->where('tags', 'like', '%' . $this->filterTag . '%');
        }
        
        $contacts = $query->latest()->paginate(10, ['*'], 'contactsPage');

        return view('livewire.admin.whatsapp-dashboard', compact(
            'sentToday', 'failedToday', 'deliveredToday', 'readToday',
            'campaignsActive', 'automationsActive', 'templates', 'automations', 'logs', 'contacts'
        ))->layout('layouts.admin');
    }
}
