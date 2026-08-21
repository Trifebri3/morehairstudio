<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\System\Models\EmailConfiguration;
use App\Domains\System\Models\EmailTemplate;
use App\Domains\System\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmailController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $activeTab = $request->get('tab', 'overview');

        // Load Global Email Setting
        $emailEnabled = true;
        $setting = DB::table('settings')->where('key', 'email_enabled')->first();
        if ($setting) {
            $emailEnabled = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        }

        // Load active SMTP configuration
        $config = EmailConfiguration::first();
        $host = '';
        $port = 587;
        $username = '';
        $password = '';
        $encryption = 'tls';
        $fromAddress = '';
        $fromName = '';
        $isActive = true;

        if ($config) {
            $host = $config->host ?? '';
            $port = $config->port ?? 587;
            $username = $config->username ?? '';
            $encryption = $config->encryption ?? 'tls';
            $fromAddress = $config->from_address ?? '';
            $fromName = $config->from_name ?? '';
            $isActive = (bool)$config->is_active;

            if (!empty($config->password)) {
                try {
                    $password = Crypt::decryptString($config->password);
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }

        // Stats
        $sentCount = EmailLog::where('status', 'SENT')->count();
        $failedCount = EmailLog::where('status', 'FAILED')->count();

        $templates = EmailTemplate::latest()->get();
        $logs = EmailLog::with('booking')->latest()->take(20)->get();

        return view('admin.email-dashboard', compact(
            'activeTab', 'emailEnabled', 'host', 'port', 'username', 'password',
            'encryption', 'fromAddress', 'fromName', 'isActive', 'sentCount',
            'failedCount', 'templates', 'logs'
        ));
    }

    public function toggleChannel(Request $request)
    {
        $enabled = $request->has('emailEnabled') ? 'true' : 'false';

        DB::table('settings')->updateOrInsert(
            ['key' => 'email_enabled'],
            ['value' => $enabled, 'type' => 'boolean', 'updated_at' => now()]
        );

        return back()->with('message', 'Status Saluran Email berhasil diperbarui.');
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'port' => 'required|integer',
            'username' => 'required',
            'from_address' => 'required|email',
            'from_name' => 'required'
        ]);

        try {
            $encryptedPassword = !empty($request->password) ? Crypt::encryptString($request->password) : null;

            EmailConfiguration::updateOrCreate(
                ['id' => 1],
                [
                    'host' => $request->host,
                    'port' => intval($request->port),
                    'username' => $request->username,
                    'password' => $encryptedPassword,
                    'encryption' => $request->encryption,
                    'from_address' => $request->from_address,
                    'from_name' => $request->from_name,
                    'is_active' => $request->has('is_active')
                ]
            );

            return redirect()->route('admin.email', ['tab' => 'settings'])->with('message', 'Konfigurasi SMTP Email berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->route('admin.email', ['tab' => 'settings'])->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function createTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:email_templates,name',
            'subject' => 'required',
            'body' => 'required'
        ]);

        EmailTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'variables' => [],
            'is_active' => true
        ]);

        return redirect()->route('admin.email', ['tab' => 'templates'])->with('message', 'Template Email baru berhasil ditambahkan.');
    }

    public function deleteTemplate($id)
    {
        EmailTemplate::destroy($id);
        return redirect()->route('admin.email', ['tab' => 'templates'])->with('message', 'Template Email berhasil dihapus.');
    }
}
