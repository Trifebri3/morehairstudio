<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\WhatsApp\Models\WhatsAppConfiguration;
use App\Domains\WhatsApp\Models\WhatsAppTemplate;
use App\Domains\WhatsApp\Models\WhatsAppAutomation;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use App\Domains\WhatsApp\Providers\CloudApiWhatsAppProvider;
use App\Domains\WhatsApp\Providers\FonnteWhatsAppProvider;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Services\PhoneNormalizer;
use App\Domains\System\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $activeTab = $request->get('tab', 'overview');

        // Load Global WhatsApp setting
        $whatsappEnabled = true;
        $setting = DB::table('settings')->where('key', 'whatsapp_enabled')->first();
        if ($setting) {
            $whatsappEnabled = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        }

        // Load Provider Settings
        $cloudToken = '';
        $cloudPhoneId = '';
        $cloudVersion = 'v20.0';
        $cloudConfig = WhatsAppConfiguration::where('provider', 'cloud_api')->first();
        if ($cloudConfig && !empty($cloudConfig->config)) {
            try {
                $decrypted = json_decode(Crypt::decryptString($cloudConfig->config), true);
                $cloudToken = $decrypted['token'] ?? '';
                $cloudPhoneId = $decrypted['phone_number_id'] ?? '';
                $cloudVersion = $decrypted['version'] ?? 'v20.0';
            } catch (\Exception $e) {}
        }

        $fonnteToken = '';
        $fonnteConfig = WhatsAppConfiguration::where('provider', 'fonnte')->first();
        if ($fonnteConfig && !empty($fonnteConfig->config)) {
            try {
                $decrypted = json_decode(Crypt::decryptString($fonnteConfig->config), true);
                $fonnteToken = $decrypted['token'] ?? '';
            } catch (\Exception $e) {}
        }

        // Detect current active provider
        $activeProvider = 'cloud_api';
        $connectionStatus = 'Not Configured';
        $activeConfig = WhatsAppConfiguration::where('is_active', true)->first();
        if ($activeConfig) {
            $activeProvider = $activeConfig->provider;
            $connectionStatus = 'Connected';
        }

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
        $searchContact = $request->get('search', '');
        $filterTag = $request->get('tag', '');
        
        $query = Customer::query()->withCount('bookings');
        if (!empty($searchContact)) {
            $query->where(function($q) use ($searchContact) {
                $q->where('name', 'like', '%' . $searchContact . '%')
                  ->orWhere('phone', 'like', '%' . $searchContact . '%');
            });
        }
        if (!empty($filterTag)) {
            $query->where('tags', 'like', '%' . $filterTag . '%');
        }
        
        $contacts = $query->latest()->paginate(10, ['*'], 'contactsPage')->withQueryString();

        return view('admin.whatsapp-dashboard', compact(
            'whatsappEnabled', 'activeProvider', 'connectionStatus',
            'cloudToken', 'cloudPhoneId', 'cloudVersion', 'fonnteToken',
            'activeTab', 'sentToday', 'failedToday', 'deliveredToday', 'readToday',
            'campaignsActive', 'automationsActive', 'templates', 'automations', 'logs', 'contacts',
            'searchContact', 'filterTag'
        ));
    }

    public function toggleChannel(Request $request)
    {
        $enabled = $request->has('whatsapp_enabled') ? 'true' : 'false';
        DB::table('settings')->updateOrInsert(
            ['key' => 'whatsapp_enabled'],
            ['value' => $enabled, 'type' => 'boolean', 'updated_at' => now()]
        );
        return back()->with('message', 'Status WhatsApp berhasil diperbarui.');
    }

    public function switchProvider($providerName)
    {
        try {
            $targetConfig = WhatsAppConfiguration::where('provider', $providerName)->first();
            
            if ($providerName === 'cloud_api') {
                if (!$targetConfig || empty($targetConfig->config)) {
                    return back()->with('error', 'Kredensial untuk provider ini belum dikonfigurasi.');
                }
                $decrypted = json_decode(Crypt::decryptString($targetConfig->config), true);
            } else {
                $tokenToUse = '';
                if ($targetConfig && !empty($targetConfig->config)) {
                    $dbConfig = json_decode(Crypt::decryptString($targetConfig->config), true);
                    $tokenToUse = $dbConfig['token'] ?? '';
                }
                if (empty($tokenToUse)) {
                    $tokenToUse = env('FONNTE_TOKEN') ?: '';
                }
                if (empty($tokenToUse)) {
                    return back()->with('error', 'Kredensial untuk provider ini belum dikonfigurasi.');
                }
                $decrypted = [
                    'token' => $tokenToUse,
                    'mock' => false
                ];
            }

            $isValid = false;
            if ($providerName === 'cloud_api') {
                $provider = new CloudApiWhatsAppProvider($decrypted);
                $isValid = $provider->validateConfiguration($decrypted);
            } else {
                $provider = new FonnteWhatsAppProvider($decrypted);
                $isValid = $provider->validateConfiguration($decrypted);
            }

            if (!$isValid) {
                return back()->with('error', 'Gagal berpindah provider: Kredensial tidak valid saat diuji koneksi.');
            }

            DB::transaction(function () use ($providerName) {
                WhatsAppConfiguration::where('is_active', true)->update(['is_active' => false]);
                WhatsAppConfiguration::updateOrCreate(
                    ['provider' => $providerName],
                    ['is_active' => true]
                );
            });

            return back()->with('message', 'Provider WhatsApp berhasil diganti ke ' . strtoupper($providerName));
        } catch (\Exception $e) {
            return back()->with('error', 'Error saat beralih: ' . $e->getMessage());
        }
    }

    public function saveCloudConfig(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'phone_number_id' => 'required',
            'version' => 'required'
        ]);

        try {
            $configData = [
                'token' => $request->token,
                'phone_number_id' => $request->phone_number_id,
                'version' => $request->version,
                'mock' => false
            ];

            $encrypted = Crypt::encryptString(json_encode($configData));

            WhatsAppConfiguration::updateOrCreate(
                ['provider' => 'cloud_api'],
                ['config' => $encrypted]
            );

            return back()->with('message', 'Konfigurasi WhatsApp Cloud API berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function saveFonnteConfig(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        try {
            $configData = [
                'token' => $request->token,
                'mock' => false
            ];

            $encrypted = Crypt::encryptString(json_encode($configData));

            WhatsAppConfiguration::updateOrCreate(
                ['provider' => 'fonnte'],
                ['config' => $encrypted]
            );

            return back()->with('message', 'Konfigurasi Fonnte berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function testConnection(Request $request, $providerName)
    {
        try {
            $decrypted = [];
            if ($providerName === 'cloud_api') {
                $tokenToTest = $request->get('token');
                $phoneIdToTest = $request->get('phone_number_id');
                $versionToTest = $request->get('version');

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
                $tokenToTest = $request->get('token');
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
                return back()->with('error', 'Koneksi Gagal: Kredensial kosong.');
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
                return back()->with('message', 'Tes Koneksi Sukses! Status: Terhubung.');
            } else {
                return back()->with('error', 'Tes Koneksi Gagal. Periksa token Anda.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Kesalahan tes: ' . $e->getMessage());
        }
    }

    public function createTemplate(Request $request)
    {
        $request->validate([
            'template_name' => 'required|unique:whatsapp_templates,template_name',
            'body' => 'required',
            'file' => 'nullable|file|max:10240'
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('whatsapp/templates', 'public');
            $filePath = '/storage/' . $path;
        }

        WhatsAppTemplate::create([
            'template_name' => $request->template_name,
            'language' => $request->get('language', 'id'),
            'body' => $request->body,
            'variables' => [],
            'is_active' => true,
            'file_path' => $filePath
        ]);

        return back()->with('message', 'Template baru berhasil ditambahkan.');
    }

    public function deleteTemplate($id)
    {
        WhatsAppTemplate::destroy($id);
        return back()->with('message', 'Template berhasil dihapus.');
    }

    public function createAutomation(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'template_name' => 'required',
            'event_type' => 'required'
        ]);

        WhatsAppAutomation::create([
            'name' => $request->name,
            'event_type' => $request->event_type,
            'template_name' => $request->template_name,
            'delay_minutes' => intval($request->get('delay_minutes', 0)),
            'is_active' => true
        ]);

        return back()->with('message', 'Aturan Otomasi berhasil dibuat.');
    }

    public function deleteAutomation($id)
    {
        WhatsAppAutomation::destroy($id);
        return back()->with('message', 'Aturan Otomasi berhasil dihapus.');
    }

    public function sendSingleMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'message' => 'required'
        ]);

        try {
            $recipient = PhoneNormalizer::normalize($request->phone);
            CommunicationService::sendWhatsApp($recipient, $request->message);
            return back()->with('message', 'Pesan WhatsApp berhasil dikirim.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim pesan: ' . $e->getMessage());
        }
    }

    public function sendBulkMessage(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'customer_ids' => 'required|array'
        ]);

        try {
            $customers = Customer::whereIn('id', $request->customer_ids)->get();
            $count = 0;

            foreach ($customers as $customer) {
                $recipient = PhoneNormalizer::normalize($customer->phone);
                
                $resolvedBody = $request->message;
                $resolvedBody = str_replace('{{customer_name}}', $customer->name, $resolvedBody);
                $resolvedBody = str_replace('{{booking_code}}', 'PROMO', $resolvedBody);

                CommunicationService::sendWhatsApp($recipient, $resolvedBody);
                $count++;
            }

            return back()->with('message', "Pesan massal berhasil dikirim ke {$count} kontak.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim pesan massal: ' . $e->getMessage());
        }
    }

    public function importContacts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120'
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $file = fopen($path, 'r');
            
            $headerLine = fgets($file);
            $separator = ',';
            if (str_contains($headerLine, ';')) {
                $separator = ';';
            }
            
            rewind($file);
            $headers = fgetcsv($file, 1000, $separator);
            if (!$headers) {
                fclose($file);
                return back()->with('error', 'File CSV kosong.');
            }

            $headers = array_map(function($h) {
                return strtolower(trim(str_replace(['"', "'"], '', $h)));
            }, $headers);

            $nameIdx = array_search('name', $headers);
            $phoneIdx = array_search('phone', $headers);
            $emailIdx = array_search('email', $headers);

            if ($nameIdx === false || $phoneIdx === false) {
                fclose($file);
                return back()->with('error', 'Format CSV salah. Harus memiliki header kolom "name" dan "phone".');
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
                    continue;
                }

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
                    if ($email && !$customer->email) {
                        $customer->update(['email' => $email]);
                    }
                }
            }

            fclose($file);
            return back()->with('message', "Berhasil mengimpor {$imported} kontak baru ke CRM.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor file CSV: ' . $e->getMessage());
        }
    }
}
