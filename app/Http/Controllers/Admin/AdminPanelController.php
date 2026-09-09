<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Service\Models\Service;
use App\Domains\Service\Models\ServiceCategory;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Customer\Models\Customer;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use App\Domains\POS\Models\PosTransaction;
use App\Domains\Outlet\Models\Outlet;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminPanelController extends Controller
{
    public function services(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        
        $activeTab = $request->get('tab', 'services');
        $search = $request->get('search', '');
        $searchCategory = $request->get('searchCategory', '');

        $services = Service::with('category')
            ->where('name', 'like', '%' . $search . '%')
            ->paginate(10, ['*'], 'servicesPage')
            ->withQueryString();
        
        $categories = ServiceCategory::all();
        $editingService = $request->has('edit') ? Service::find($request->edit) : null;
        $isCreating = $request->has('create');

        $paginatedCategories = ServiceCategory::where('name', 'like', '%' . $searchCategory . '%')
            ->paginate(10, ['*'], 'categoriesPage')
            ->withQueryString();

        $editingCategory = $request->has('edit_category') ? ServiceCategory::find($request->edit_category) : null;
        $isCreatingCategory = $request->has('create_category');

        return view('admin.services', compact(
            'services', 'search', 'categories', 'editingService', 'isCreating',
            'paginatedCategories', 'searchCategory', 'editingCategory', 'isCreatingCategory', 'activeTab'
        ));
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:services,slug',
            'service_category_id' => 'required|exists:service_categories,id',
            'default_price' => 'required|numeric|min:0',
            'default_duration' => 'required|integer|min:5',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string'
        ]);

        Service::create($request->all());
        return redirect()->route('admin.services', ['tab' => 'services'])->with('message', 'Layanan berhasil ditambahkan.');
    }

    public function updateService(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:services,slug,' . $id,
            'service_category_id' => 'required|exists:service_categories,id',
            'default_price' => 'required|numeric|min:0',
            'default_duration' => 'required|integer|min:5',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string'
        ]);

        $service->update($request->all());
        return redirect()->route('admin.services', ['tab' => 'services'])->with('message', 'Layanan berhasil diperbarui.');
    }

    public function toggleServiceStatus($id)
    {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();
        return back()->with('message', 'Status layanan berhasil diubah.');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:service_categories,slug',
            'description' => 'nullable|string'
        ]);

        ServiceCategory::create($request->all());
        return redirect()->route('admin.services', ['tab' => 'categories'])->with('message', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = ServiceCategory::findOrFail($id);
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:service_categories,slug,' . $id,
            'description' => 'nullable|string'
        ]);

        $category->update($request->all());
        return redirect()->route('admin.services', ['tab' => 'categories'])->with('message', 'Kategori berhasil diperbarui.');
    }

    public function deleteCategory($id)
    {
        $category = ServiceCategory::findOrFail($id);
        
        if (Service::where('service_category_id', $category->id)->exists()) {
            return redirect()->route('admin.services', ['tab' => 'categories'])
                ->with('error', "Kategori {$category->name} tidak dapat dihapus karena memiliki layanan aktif terikat.");
        }

        $category->delete();
        return redirect()->route('admin.services', ['tab' => 'categories'])->with('message', 'Kategori berhasil dihapus.');
    }

    public function stylists(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        $search = $request->get('search', '');
        $stylists = Stylist::with(['outlet', 'user'])
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('specialization', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', '%' . $search . '%');
                  });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $outlets = Outlet::all();
        $editingStylist = $request->has('edit') ? Stylist::with('user')->find($request->edit) : null;
        $isCreating = $request->has('create');

        return view('admin.stylists', compact('stylists', 'search', 'outlets', 'editingStylist', 'isCreating'));
    }

    public function storeStylist(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stylists,slug',
            'outlet_id' => 'required|exists:outlets,id',
            'phone' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,pending_active,pending_inactive',
            'bio' => 'nullable|string|max:1000',
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6'
        ], [
            'email.unique' => 'Email akun sudah digunakan oleh pengguna lain.',
            'photo.image' => 'File foto profil wajib berformat gambar (JPG, PNG, WEBP).',
            'photo.max' => 'Ukuran foto profil maksimal 3MB.',
        ]);

        // 1. Create User login account
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?: 'password123'),
            'role' => 'stylist',
            'outlet_id' => $request->outlet_id,
        ]);

        // 2. Handle Photo Upload
        $photoPath = null;
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('stylists', 'public');
            $photoUrl = '/storage/' . $photoPath;
        }

        // Clean phone
        $phone = $request->phone;
        if ($phone && str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // 3. Create Stylist profile
        $newStylist = Stylist::create([
            'user_id' => $user->id,
            'outlet_id' => $request->outlet_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'specialization' => $request->specialization,
            'phone' => $phone,
            'status' => $request->status,
            'bio' => $request->bio,
            'instagram' => $request->instagram ? ltrim($request->instagram, '@') : null,
            'tiktok' => $request->tiktok ? ltrim($request->tiktok, '@') : null,
            'photo_path' => $photoPath,
            'photo' => $photoUrl,
            'rating' => 5.00,
        ]);

        // Auto-seed default 7-day working schedules
        for ($day = 0; $day <= 6; $day++) {
            \App\Domains\Stylist\Models\StylistSchedule::create([
                'stylist_id' => $newStylist->id,
                'day_of_week' => $day,
                'start_time' => '10:00:00',
                'end_time' => '20:00:00',
                'break_start' => null,
                'break_end' => null,
                'is_working' => true,
            ]);
        }

        return redirect()->route('admin.stylists')->with('message', 'Stylist & Akun Login berhasil dibuat.');
    }

    public function updateStylist(Request $request, $id)
    {
        $stylist = Stylist::with('user')->findOrFail($id);
        $user = $stylist->user;
        $userId = $user?->id;

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stylists,slug,' . $id,
            'outlet_id' => 'required|exists:outlets,id',
            'phone' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,pending_active,pending_inactive',
            'bio' => 'nullable|string|max:1000',
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($userId ?: 'NULL'),
            'password' => 'nullable|string|min:6'
        ], [
            'email.unique' => 'Email akun sudah digunakan oleh pengguna lain.',
            'photo.image' => 'File foto profil wajib berformat gambar (JPG, PNG, WEBP).',
            'photo.max' => 'Ukuran foto profil maksimal 3MB.',
        ]);

        // 1. Handle Photo Upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('stylists', 'public');
            $stylist->photo_path = $photoPath;
            $stylist->photo = '/storage/' . $photoPath;
        }

        // 2. Sync User Account
        if ($user) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'outlet_id' => $request->outlet_id,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password ?: 'password123'),
                'role' => 'stylist',
                'outlet_id' => $request->outlet_id,
            ]);
            $stylist->user_id = $user->id;
        }

        // Clean phone
        $phone = $request->phone;
        if ($phone && str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // 3. Update Stylist profile
        $stylist->name = $request->name;
        $stylist->slug = $request->slug;
        $stylist->outlet_id = $request->outlet_id;
        $stylist->phone = $phone;
        $stylist->specialization = $request->specialization;
        $stylist->status = $request->status;
        $stylist->bio = $request->bio;
        $stylist->instagram = $request->instagram ? ltrim($request->instagram, '@') : null;
        $stylist->tiktok = $request->tiktok ? ltrim($request->tiktok, '@') : null;
        $stylist->save();

        return redirect()->route('admin.stylists')->with('message', 'Stylist & Akun Login berhasil diperbarui.');
    }

    public function customers(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        $search = $request->get('search', '');
        $customers = Customer::where('name', 'like', '%' . $search . '%')
            ->orWhere('phone', 'like', '%' . $search . '%')
            ->paginate(10)
            ->withQueryString();

        $editingCustomer = $request->has('edit') ? Customer::find($request->edit) : null;
        $isCreating = $request->has('create');

        return view('admin.customers', compact('customers', 'search', 'editingCustomer', 'isCreating'));
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
        ]);

        $total = Customer::count() + 1;
        $custCode = 'CUST-' . str_pad((string)$total, 5, '0', STR_PAD_LEFT);

        Customer::create(array_merge($request->all(), [
            'customer_code' => $custCode,
            'whatsapp_phone' => $request->phone,
            'whatsapp_marketing_opt_in' => true,
            'email_marketing_opt_in' => true
        ]));

        return redirect()->route('admin.customers')->with('message', 'Pelanggan berhasil ditambahkan.');
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $id,
            'email' => 'nullable|email|unique:customers,email,' . $id,
        ]);

        $customer->update($request->all());
        return redirect()->route('admin.customers')->with('message', 'Pelanggan berhasil diperbarui.');
    }

    public function deleteCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $customerName = $customer->name;
        $result = AccountDeletionService::deleteCustomerAccount($customer, 'Dihapus oleh Admin melalui Panel CRM');

        $message = "Data & akun pelanggan '{$customerName}' berhasil dihapus dan dianonimkan secara aman.";
        if (!empty($result['cancelled_bookings']) && $result['cancelled_bookings'] > 0) {
            $message .= " ({$result['cancelled_bookings']} reservasi mendatang otomatis dibatalkan).";
        }

        return redirect()->route('admin.customers')->with('message', $message);
    }

    public function deleteStylist($id)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }

        $stylist = Stylist::with('user')->findOrFail($id);
        $stylistName = $stylist->name;

        try {
            AccountDeletionService::deleteStylistAccount($stylist, 'Dihapus oleh Super Admin dari Panel Manajemen Stylist');
            return redirect()->route('admin.stylists')->with('message', "Akun login dan profil stylist '{$stylistName}' berhasil dihapus & dinonaktifkan secara aman.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMsg = $e->validator->errors()->first('stylist') ?? 'Tidak dapat menghapus stylist.';
            return redirect()->route('admin.stylists')->with('error', $errorMsg);
        }
    }

    public function downloadStylistTemplate(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }

        $outlets = Outlet::all();
        $format = strtolower($request->get('format', 'xlsx'));

        if ($format === 'csv') {
            $fileName = 'Template_Import_Stylist_MORE.csv';
            return response()->streamDownload(function () use ($outlets) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Microsoft Excel
                fputs($file, "\xEF\xBB\xBF");

                // Headers
                fputcsv($file, [
                    'Nama Stylist (Wajib)',
                    'Email Login (Wajib)',
                    'Password Akun (Default: password123)',
                    'Outlet Studio (Nama atau ID Outlet)',
                    'Spesialisasi',
                    'Nomor WhatsApp',
                    'Instagram',
                    'TikTok',
                    'Bio Singkat',
                    'Status (active / inactive)'
                ]);

                // Sample Rows
                $defaultOutletName = $outlets->first()?->name ?? 'MORE Hair Studio';
                fputcsv($file, [
                    'Raka Pratama',
                    'raka.pratama@morehair.com',
                    'password123',
                    $defaultOutletName,
                    'Haircut & Styling',
                    '081234567890',
                    '@raka_styling',
                    '@raka_styling',
                    'Senior Stylist spesialis modern scissor cut dan men grooming',
                    'active'
                ]);

                fputcsv($file, [
                    'Dimas Setiawan',
                    'dimas.setiawan@morehair.com',
                    'password123',
                    $defaultOutletName,
                    'Colorist & Bleaching',
                    '082198765432',
                    '@dimas_haircolor',
                    '@dimas_haircolor',
                    'Spesialis balayage, highlight, scalp care, and coloring',
                    'active'
                ]);

                fclose($file);
            }, $fileName, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        // Generate Excel (.xlsx) using PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('MORE Hair Studio')
            ->setTitle('Template Import Stylist & Akun')
            ->setSubject('Template Import Stylist')
            ->setDescription('Template impor data hair stylist dan akun login MORE Hair Studio.');

        // ------------------ SHEET 1: TEMPLATE DATA ------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Stylist');

        $headers = [
            'A1' => 'Nama Stylist (Wajib)',
            'B1' => 'Email Login (Wajib)',
            'C1' => 'Password Akun (Default: password123)',
            'D1' => 'Outlet Studio (Nama atau ID Outlet)',
            'E1' => 'Spesialisasi',
            'F1' => 'Nomor WhatsApp',
            'G1' => 'Instagram',
            'H1' => 'TikTok',
            'I1' => 'Bio Singkat',
            'J1' => 'Status (active / inactive)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Style (MORE Navy Blue, White Bold, Centered)
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0A3D91'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Sample Data Rows
        $defaultOutletName = $outlets->first()?->name ?? 'MORE Hair Studio';
        $sampleData = [
            [
                'Raka Pratama',
                'raka.pratama@morehair.com',
                'password123',
                $defaultOutletName,
                'Haircut & Styling',
                '081234567890',
                '@raka_styling',
                '@raka_styling',
                'Senior Stylist spesialis modern scissor cut dan men grooming',
                'active',
            ],
            [
                'Dimas Setiawan',
                'dimas.setiawan@morehair.com',
                'password123',
                $defaultOutletName,
                'Colorist & Bleaching',
                '082198765432',
                '@dimas_haircolor',
                '@dimas_haircolor',
                'Spesialis balayage, highlight, scalp care, and coloring',
                'active',
            ],
        ];

        $sheet->fromArray($sampleData, null, 'A2');
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(22);
        $rowNum = 4;

        // Data rows border and text format
        $dataRange = 'A2:J' . ($rowNum - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE5E7EB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-fit column widths
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ------------------ SHEET 2: DAFTAR OUTLET & PETUNJUK ------------------
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Petunjuk & Outlet');

        // Instructions
        $instructionSheet->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE IMPORT STYLIST');
        $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FF0A3D91');
        $instructionSheet->getRowDimension(1)->setRowHeight(25);

        $notes = [
            '1. Kolom "Nama Stylist" dan "Email Login" wajib diisi untuk setiap baris data stylist.',
            '2. Email Login harus unik (belum pernah terdaftar di sistem). Akun ini digunakan stylist untuk login ke Dasbor Stylist.',
            '3. Password Akun bersifat opsional; jika dikosongkan akan otomatis disetel ke "password123".',
            '4. Kolom "Outlet Studio" dapat diisi Nama Outlet atau ID Outlet dari tabel di bawah. Jika dikosongkan, akan menggunakan outlet default yang dipilih saat upload file.',
            '5. Nomor WhatsApp dapat diawali dengan "08" atau "628" (sistem otomatis menstandarkan ke awalan 62).',
            '6. Kolom Status dapat diisi "active" (aktif melayani) atau "inactive" (cuti/tidak aktif). Default adalah "active".',
            '7. Setiap stylist yang berhasil diimpor otomatis dibuatkan jadwal kerja mingguan standar (Senin s/d Minggu, 10:00 - 20:00).',
        ];

        $noteRow = 3;
        foreach ($notes as $note) {
            $instructionSheet->setCellValue('A' . $noteRow, $note);
            $instructionSheet->getStyle('A' . $noteRow)->getFont()->setSize(10);
            $noteRow++;
        }

        // Outlet Table
        $tableStartRow = $noteRow + 2;
        $instructionSheet->setCellValue('A' . $tableStartRow, 'DAFTAR OUTLET TERSEDIA DI SISTEM');
        $instructionSheet->getStyle('A' . $tableStartRow)->getFont()->setBold(true)->setSize(11);
        
        $tableHeaderRow = $tableStartRow + 1;
        $instructionSheet->setCellValue('A' . $tableHeaderRow, 'ID Outlet');
        $instructionSheet->setCellValue('B' . $tableHeaderRow, 'Nama Outlet');
        $instructionSheet->setCellValue('C' . $tableHeaderRow, 'Alamat Studio');
        $instructionSheet->getStyle("A{$tableHeaderRow}:C{$tableHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0A3D91'],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $instructionSheet->getRowDimension($tableHeaderRow)->setRowHeight(24);

        $outRow = $tableHeaderRow + 1;
        foreach ($outlets as $o) {
            $instructionSheet->setCellValue('A' . $outRow, $o->id);
            $instructionSheet->setCellValue('B' . $outRow, $o->name);
            $instructionSheet->setCellValue('C' . $outRow, $o->address ?? '-');
            $instructionSheet->getRowDimension($outRow)->setRowHeight(20);
            $outRow++;
        }

        $instructionSheet->getStyle("A{$tableHeaderRow}:C" . ($outRow - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
        ]);

        foreach (['A', 'B', 'C'] as $col) {
            $instructionSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set active sheet back to Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'Template_Import_Stylist_MORE.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function exportStylists(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }

        $stylists = Stylist::with(['outlet', 'user'])->latest()->get();
        $format = strtolower($request->get('format', 'xlsx'));

        if ($format === 'csv') {
            $fileName = 'morehair_stylists_export_' . date('Ymd_His') . '.csv';
            return response()->streamDownload(function () use ($stylists) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, [
                    'No', 'Nama Stylist', 'Slug Profil', 'Email Akun Login', 'Outlet Studio',
                    'Spesialisasi', 'Nomor WhatsApp', 'Instagram', 'TikTok', 'Status',
                    'Bio', 'Rating', 'Tanggal Terdaftar'
                ]);

                $i = 1;
                foreach ($stylists as $s) {
                    fputcsv($file, [
                        $i++,
                        $s->name,
                        $s->slug,
                        $s->user?->email ?? '-',
                        $s->outlet?->name ?? '-',
                        $s->specialization ?? '-',
                        $s->phone ?? '-',
                        $s->instagram ? '@' . ltrim($s->instagram, '@') : '-',
                        $s->tiktok ? '@' . ltrim($s->tiktok, '@') : '-',
                        $s->status,
                        $s->bio ?? '-',
                        $s->rating ?? '5.00',
                        $s->created_at ? $s->created_at->format('Y-m-d H:i') : '-'
                    ]);
                }
                fclose($file);
            }, $fileName, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Stylist');

        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Stylist',
            'C1' => 'Slug Profil',
            'D1' => 'Email Akun Login',
            'E1' => 'Outlet Studio',
            'F1' => 'Spesialisasi',
            'G1' => 'Nomor WhatsApp',
            'H1' => 'Instagram',
            'I1' => 'TikTok',
            'J1' => 'Status',
            'K1' => 'Bio',
            'L1' => 'Rating',
            'M1' => 'Tanggal Terdaftar',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0A3D91'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $i = 1;
        foreach ($stylists as $s) {
            $sheet->setCellValue('A' . $rowNum, $i++);
            $sheet->setCellValue('B' . $rowNum, $s->name);
            $sheet->setCellValue('C' . $rowNum, $s->slug);
            $sheet->setCellValue('D' . $rowNum, $s->user?->email ?? '-');
            $sheet->setCellValue('E' . $rowNum, $s->outlet?->name ?? '-');
            $sheet->setCellValue('F' . $rowNum, $s->specialization ?? '-');
            $sheet->setCellValue('G' . $rowNum, $s->phone ?? '-');
            $sheet->setCellValue('H' . $rowNum, $s->instagram ? '@' . ltrim($s->instagram, '@') : '-');
            $sheet->setCellValue('I' . $rowNum, $s->tiktok ? '@' . ltrim($s->tiktok, '@') : '-');
            $sheet->setCellValue('J' . $rowNum, strtoupper($s->status));
            $sheet->setCellValue('K' . $rowNum, $s->bio ?? '-');
            $sheet->setCellValue('L' . $rowNum, $s->rating ?? '5.00');
            $sheet->setCellValue('M' . $rowNum, $s->created_at ? $s->created_at->format('Y-m-d H:i') : '-');

            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
        }

        if ($rowNum > 2) {
            $sheet->getStyle('A2:M' . ($rowNum - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFE5E7EB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'morehair_stylists_export_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function importStylists(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }

        $request->validate([
            'file' => 'required|file|max:10240',
            'default_outlet_id' => 'nullable|exists:outlets,id',
        ], [
            'file.required' => 'Silakan pilih file Excel (.xlsx) atau CSV untuk diunggah.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = [];

        try {
            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray(null, true, true, false);
            } else {
                // CSV fallback
                $handle = fopen($filePath, 'r');
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                while (($data = fgetcsv($handle, 10000, ',')) !== false) {
                    if (count($data) === 1 && str_contains($data[0], ';')) {
                        $data = str_getcsv($data[0], ';');
                    }
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.stylists')->with('error', 'Gagal membaca file data: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return redirect()->route('admin.stylists')->with('error', 'File tidak memiliki baris data atau hanya berisi judul kolom.');
        }

        // Parse header row
        $headerRow = array_map(function ($h) {
            return strtolower(trim((string)$h));
        }, $rows[0]);

        $columnMap = [
            'name' => null,
            'email' => null,
            'password' => null,
            'outlet' => null,
            'specialization' => null,
            'phone' => null,
            'instagram' => null,
            'tiktok' => null,
            'bio' => null,
            'status' => null,
        ];

        foreach ($headerRow as $idx => $header) {
            if (str_contains($header, 'nama')) {
                $columnMap['name'] = $idx;
            } elseif (str_contains($header, 'email')) {
                $columnMap['email'] = $idx;
            } elseif (str_contains($header, 'pass')) {
                $columnMap['password'] = $idx;
            } elseif (str_contains($header, 'outlet') || str_contains($header, 'studio')) {
                $columnMap['outlet'] = $idx;
            } elseif (str_contains($header, 'spesialis') || str_contains($header, 'keahlian')) {
                $columnMap['specialization'] = $idx;
            } elseif (str_contains($header, 'wa') || str_contains($header, 'phone') || str_contains($header, 'telepon') || str_contains($header, 'whatsapp')) {
                $columnMap['phone'] = $idx;
            } elseif (str_contains($header, 'ig') || str_contains($header, 'instagram')) {
                $columnMap['instagram'] = $idx;
            } elseif (str_contains($header, 'tt') || str_contains($header, 'tiktok')) {
                $columnMap['tiktok'] = $idx;
            } elseif (str_contains($header, 'bio') || str_contains($header, 'deskripsi')) {
                $columnMap['bio'] = $idx;
            } elseif (str_contains($header, 'status')) {
                $columnMap['status'] = $idx;
            }
        }

        // Fallback default index positions if headers did not match
        if ($columnMap['name'] === null) $columnMap['name'] = 0;
        if ($columnMap['email'] === null) $columnMap['email'] = 1;
        if ($columnMap['password'] === null) $columnMap['password'] = 2;
        if ($columnMap['outlet'] === null) $columnMap['outlet'] = 3;
        if ($columnMap['specialization'] === null) $columnMap['specialization'] = 4;
        if ($columnMap['phone'] === null) $columnMap['phone'] = 5;
        if ($columnMap['instagram'] === null) $columnMap['instagram'] = 6;
        if ($columnMap['tiktok'] === null) $columnMap['tiktok'] = 7;
        if ($columnMap['bio'] === null) $columnMap['bio'] = 8;
        if ($columnMap['status'] === null) $columnMap['status'] = 9;

        $outlets = Outlet::all();
        $defaultOutletId = $request->default_outlet_id ?: ($outlets->first()?->id ?? 1);

        $successCount = 0;
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNum = $i + 1;

            $nonEmpty = array_filter($row, function ($val) {
                return trim((string)$val) !== '';
            });
            if (empty($nonEmpty)) {
                continue;
            }

            $name = trim((string)($row[$columnMap['name']] ?? ''));
            $email = trim((string)($row[$columnMap['email']] ?? ''));
            $password = trim((string)($row[$columnMap['password']] ?? ''));
            $outletVal = trim((string)($row[$columnMap['outlet']] ?? ''));
            $specialization = trim((string)($row[$columnMap['specialization']] ?? ''));
            $phone = trim((string)($row[$columnMap['phone']] ?? ''));
            $instagram = trim((string)($row[$columnMap['instagram']] ?? ''));
            $tiktok = trim((string)($row[$columnMap['tiktok']] ?? ''));
            $bio = trim((string)($row[$columnMap['bio']] ?? ''));
            $status = strtolower(trim((string)($row[$columnMap['status']] ?? '')));

            // Validation 1: Required Name & Email
            if (empty($name)) {
                $errors[] = "Baris $rowNum: Kolom Nama Stylist wajib diisi.";
                continue;
            }
            if (empty($email)) {
                $errors[] = "Baris $rowNum: Kolom Email Akun Login untuk stylist '{$name}' wajib diisi.";
                continue;
            }

            // Validation 2: Email Format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Baris $rowNum: Format email '{$email}' tidak valid.";
                continue;
            }

            // Validation 3: Unique Email
            if (User::where('email', $email)->exists()) {
                $errors[] = "Baris $rowNum: Email '{$email}' sudah digunakan oleh pengguna lain.";
                continue;
            }

            // Resolve Outlet
            $outletId = $defaultOutletId;
            if (!empty($outletVal)) {
                if (is_numeric($outletVal) && $outlets->contains('id', (int)$outletVal)) {
                    $outletId = (int)$outletVal;
                } else {
                    $matchedOutlet = $outlets->first(function ($o) use ($outletVal) {
                        return str_contains(strtolower($o->name), strtolower($outletVal)) || str_contains(strtolower($outletVal), strtolower($o->name));
                    });
                    if ($matchedOutlet) {
                        $outletId = $matchedOutlet->id;
                    }
                }
            }

            // Normalize Status
            $validStatuses = ['active', 'inactive', 'pending_active', 'pending_inactive'];
            $status = in_array($status, $validStatuses) ? $status : 'active';

            // Clean Phone (e.g. 0812... -> 62812...)
            if (!empty($phone)) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($phone, '0')) {
                    $phone = '62' . substr($phone, 1);
                }
            } else {
                $phone = null;
            }

            // Clean Instagram & TikTok handles
            $instagram = !empty($instagram) ? ltrim($instagram, '@') : null;
            $tiktok = !empty($tiktok) ? ltrim($tiktok, '@') : null;

            // Generate Unique Slug
            $baseSlug = Str::slug($name);
            if (empty($baseSlug)) {
                $baseSlug = 'stylist-' . Str::random(5);
            }
            $slug = $baseSlug;
            $counter = 2;
            while (Stylist::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            try {
                DB::transaction(function () use ($name, $email, $password, $outletId, $specialization, $phone, $status, $bio, $instagram, $tiktok, $slug) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make(!empty($password) ? $password : 'password123'),
                        'role' => 'stylist',
                        'outlet_id' => $outletId,
                    ]);

                    $stylist = Stylist::create([
                        'user_id' => $user->id,
                        'outlet_id' => $outletId,
                        'name' => $name,
                        'slug' => $slug,
                        'specialization' => !empty($specialization) ? $specialization : 'Haircut & Styling',
                        'phone' => $phone,
                        'status' => $status,
                        'bio' => !empty($bio) ? $bio : null,
                        'instagram' => $instagram,
                        'tiktok' => $tiktok,
                        'rating' => 5.00,
                    ]);

                    for ($day = 0; $day <= 6; $day++) {
                        \App\Domains\Stylist\Models\StylistSchedule::create([
                            'stylist_id' => $stylist->id,
                            'day_of_week' => $day,
                            'start_time' => '10:00:00',
                            'end_time' => '20:00:00',
                            'break_start' => null,
                            'break_end' => null,
                            'is_working' => true,
                        ]);
                    }
                });

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Baris $rowNum: Gagal menyimpan data ({$e->getMessage()}).";
            }
        }

        $msg = "Berhasil mengimpor {$successCount} data stylist dan akun login baru.";
        if (count($errors) > 0) {
            $msg .= " Terdapat " . count($errors) . " baris yang dilewati karena tidak memenuhi validasi.";
            session()->flash('import_errors', $errors);
        }

        return redirect()->route('admin.stylists')->with('message', $msg);
    }

    public function promotions(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        $search = $request->get('search', '');
        $promotions = Promotion::where('promo_code', 'like', '%' . $search . '%')
            ->paginate(10)
            ->withQueryString();

        $editingPromotion = $request->has('edit') ? Promotion::find($request->edit) : null;
        $isCreating = $request->has('create');

        return view('admin.promotions', compact('promotions', 'search', 'editingPromotion', 'isCreating'));
    }

    public function storePromotion(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string|unique:promotions,promo_code',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'minimum_transaction' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ]);

        Promotion::create($request->all());
        return redirect()->route('admin.promotions')->with('message', 'Promosi berhasil ditambahkan.');
    }

    public function updatePromotion(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        $request->validate([
            'promo_code' => 'required|string|unique:promotions,promo_code,' . $id,
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'minimum_transaction' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ]);

        $promotion->update($request->all());
        return redirect()->route('admin.promotions')->with('message', 'Promosi berhasil diperbarui.');
    }

    public function deletePromotion($id)
    {
        Promotion::destroy($id);
        return redirect()->route('admin.promotions')->with('message', 'Promosi berhasil dihapus.');
    }

    public function crm(Request $request)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'outlet_admin') { 
            return redirect()->route('dashboard'); 
        }
        
        Gate::authorize('customer.view');

        // Filter parameters
        $filterOutlet = $request->get('filterOutlet', '');
        $filterSource = $request->get('filterSource', '');
        $filterSegment = $request->get('filterSegment', '');
        $dateFrom = $request->get('dateFrom', '');
        $dateTo = $request->get('dateTo', '');
        $search = $request->get('search', '');

        // Apply Multi-outlet filter isolation
        if (auth()->user()->role === 'outlet_admin') {
            $filterOutlet = auth()->user()->outlet_id;
        }

        // Fetch customer list with search/filter
        $query = Customer::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('customer_code', 'like', '%' . $search . '%');
            });
        }

        if ($filterOutlet) {
            $query->where(function ($q) use ($filterOutlet) {
                $q->whereHas('bookings', function ($b) use ($filterOutlet) {
                    $b->where('outlet_id', $filterOutlet);
                })->orWhereHas('posTransactions', function ($p) use ($filterOutlet) {
                    $p->where('outlet_id', $filterOutlet);
                });
            });
        }

        if ($filterSource) {
            $query->where('first_acquisition_source', $filterSource);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $allRawCustomers = $query->with([
            'bookings' => fn($q) => $q->where('status', 'completed')->select('id', 'customer_id', 'booking_date', 'net_amount', 'status'),
            'posTransactions' => fn($q) => $q->where('status', 'completed')->select('id', 'customer_id', 'completed_at', 'grand_total', 'status')
        ])->get();

        // Hydrate RFM segments in collection with zero N+1 database queries
        $customerList = $allRawCustomers->map(function ($c) {
            $rfm = \App\Domains\CRM\Services\RFMService::analyze($c);
            
            // Fast retention status directly from recency without extra queries
            $recency = $rfm['recency_days'];
            if ($recency > 180) {
                $retentionStatus = 'Lost';
            } elseif ($recency > 90) {
                $retentionStatus = 'At Risk';
            } elseif ($recency > 45) {
                $retentionStatus = 'Inactive';
            } else {
                $retentionStatus = 'Active';
            }

            $c->rfm_segment = $rfm['segment'];
            $c->rfm_score = $rfm['rfm_code'];
            $c->total_spending = $rfm['total_spending'];
            $c->total_visits = $rfm['total_visits'];
            $c->retention_status = $retentionStatus;
            return $c;
        });

        // Filter segment in collection (calculated value)
        if ($filterSegment) {
            $customerList = $customerList->filter(function ($c) use ($filterSegment) {
                return strtolower($c->rfm_segment) === strtolower($filterSegment);
            });
        }

        // Compute dashboard metrics
        $totalCustomers = $customerList->count();
        $newCustomers = $customerList->filter(function ($c) {
            return $c->created_at->gte(\Carbon\Carbon::now()->subDays(30));
        })->count();

        $activeCount = $customerList->filter(fn($c) => $c->retention_status === 'Active')->count();
        $inactiveCount = $customerList->filter(fn($c) => $c->retention_status === 'Inactive')->count();
        $atRiskCount = $customerList->filter(fn($c) => $c->retention_status === 'At Risk')->count();
        $lostCount = $customerList->filter(fn($c) => $c->retention_status === 'Lost')->count();

        $repeatCustomers = $customerList->filter(fn($c) => $c->total_visits >= 2)->count();
        $repeatRate = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100) : 0;
        
        $totalCSpend = $customerList->sum('total_spending');
        $avgSpending = $totalCustomers > 0 ? round($totalCSpend / $totalCustomers) : 0;

        // Retention Rate calculation (active + new / total)
        $retentionRate = $totalCustomers > 0 ? round((($activeCount + $newCustomers) / $totalCustomers) * 100) : 0;

        // Dynamic Segment breakdown
        $segmentBreakdown = $customerList->groupBy('rfm_segment')->map->count()->toArray();
        // Dynamic Source breakdown
        $sourceBreakdown = $customerList->groupBy('first_acquisition_source')->map->count()->toArray();

        // Selected Customer Details Timeline
        $selectedCustomerId = $request->get('customer_id');
        $selectedCustomer = null;
        $timeline = [];
        if ($selectedCustomerId) {
            $selectedCustomer = Customer::findOrFail($selectedCustomerId);
            
            // Get behavioral data
            $selectedCustomer->behavior = \App\Domains\CRM\Services\CRMAnalyticsService::getBehavior($selectedCustomer);
            $selectedCustomer->rfm = \App\Domains\CRM\Services\RFMService::analyze($selectedCustomer);

            // Fetch customer timeline activities
            $timeline = $selectedCustomer->activities()->orderBy('event_date', 'desc')->get();
        }

        $outlets = Outlet::all();

        return view('admin.crm', compact(
            'customerList', 'totalCustomers', 'newCustomers', 'activeCount',
            'inactiveCount', 'atRiskCount', 'lostCount', 'repeatRate', 'avgSpending',
            'retentionRate', 'segmentBreakdown', 'sourceBreakdown', 'selectedCustomer',
            'timeline', 'outlets', 'filterOutlet', 'filterSource', 'filterSegment',
            'dateFrom', 'dateTo', 'search', 'selectedCustomerId'
        ));
    }

    public function exportCrm(Request $request)
    {
        Gate::authorize('customer.export');

        $filterOutlet = $request->get('filterOutlet', '');
        $filterSource = $request->get('filterSource', '');
        $filterSegment = $request->get('filterSegment', '');
        $dateFrom = $request->get('dateFrom', '');
        $dateTo = $request->get('dateTo', '');

        if (auth()->user()->role === 'outlet_admin') {
            $filterOutlet = auth()->user()->outlet_id;
        }

        $query = Customer::query();

        if ($filterOutlet) {
            $query->where(function ($q) use ($filterOutlet) {
                $q->whereHas('bookings', function ($b) use ($filterOutlet) {
                    $b->where('outlet_id', $filterOutlet);
                })->orWhereHas('posTransactions', function ($p) use ($filterOutlet) {
                    $p->where('outlet_id', $filterOutlet);
                });
            });
        }

        if ($filterSource) {
            $query->where('first_acquisition_source', $filterSource);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $customers = $query->get();

        if ($filterSegment) {
            $customers = $customers->filter(function ($c) use ($filterSegment) {
                $rfm = \App\Domains\CRM\Services\RFMService::analyze($c);
                return strtolower($rfm['segment']) === strtolower($filterSegment);
            });
        }

        $fileName = 'morehair_crm_export_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        \App\Domains\System\Services\AuditLogger::log('customer.export', null, null, null, ['record_count' => $customers->count()]);

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(239) . chr(187) . chr(191));
            fputs($file, "sep=;\n");

            fputcsv($file, [
                'ID Pelanggan', 'Kode Pelanggan', 'Nama', 'Telepon', 'WhatsApp', 'Email',
                'Gender', 'Tanggal Lahir', 'Loyalty Points', 'Sumber Pertama', 'Sumber Terbaru',
                'Kunjungan Pertama', 'Kunjungan Terakhir', 'Total Kunjungan', 'Total Belanja (IDR)',
                'Rata-rata Belanja (IDR)', 'Segmen RFM', 'Status', 'Tanggal Daftar'
            ], ';');

            foreach ($customers as $c) {
                $rfm = \App\Domains\CRM\Services\RFMService::analyze($c);
                $behavior = \App\Domains\CRM\Services\CRMAnalyticsService::getBehavior($c);

                fputcsv($file, [
                    $c->id, $c->customer_code, $c->name, $c->phone, $c->whatsapp_phone ?: '-', $c->email ?: '-',
                    $c->gender ?: '-', $c->birth_date ? $c->birth_date->toDateString() : '-', $c->loyalty_points,
                    $c->first_acquisition_source, $c->latest_acquisition_source, $behavior['first_visit'] ?: '-',
                    $behavior['last_visit'] ?: '-', $behavior['total_visits'], $behavior['total_spending'],
                    $behavior['average_spending'], $rfm['segment'], $c->status, $c->created_at->toDateTimeString()
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    public function whatsappLogs(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        $logs = WhatsAppMessage::with('booking')->latest()->paginate(15);
        return view('admin.whatsapp-logs', compact('logs'));
    }

    public function analytics(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        // 1. High-level totals
        $totalBookings = \App\Domains\Booking\Models\Booking::count();
        $completedBookings = \App\Domains\Booking\Models\Booking::where('status', 'completed')->count();
        $totalRevenue = \App\Domains\Booking\Models\Booking::whereIn('status', ['completed', 'checked_in', 'in_progress'])
            ->sum('net_amount');
        $totalCustomers = Customer::count();
        $averageRating = \App\Domains\Review\Models\Review::avg('rating') ?: 5.0;

        // 2. Outlet breakdown (Revenue & Bookings count with grouped SQL)
        $outletRevenues = \App\Domains\Booking\Models\Booking::whereIn('status', ['completed', 'checked_in', 'in_progress'])
            ->select('outlet_id', DB::raw('sum(net_amount) as total_rev'))
            ->groupBy('outlet_id')
            ->pluck('total_rev', 'outlet_id');

        $outletStats = Outlet::withCount('bookings')
            ->get()
            ->map(function ($outlet) use ($outletRevenues) {
                return [
                    'name' => $outlet->name,
                    'bookings_count' => $outlet->bookings_count,
                    'revenue' => (float)($outletRevenues[$outlet->id] ?? 0)
                ];
            });

        // 3. Stylist rating & bookings breakdown with grouped SQL
        $stylistRatings = \App\Domains\Review\Models\Review::join('bookings', 'reviews.booking_id', '=', 'bookings.id')
            ->select('bookings.stylist_id', DB::raw('avg(reviews.rating) as avg_rating'))
            ->whereNotNull('bookings.stylist_id')
            ->groupBy('bookings.stylist_id')
            ->pluck('avg_rating', 'bookings.stylist_id');

        $stylistStats = Stylist::withCount('bookings')
            ->get()
            ->map(function ($stylist) use ($stylistRatings) {
                $rating = isset($stylistRatings[$stylist->id]) ? round((float)$stylistRatings[$stylist->id], 1) : $stylist->rating;
                return [
                    'name' => $stylist->name,
                    'bookings_count' => $stylist->bookings_count,
                    'rating' => $rating,
                    'specialization' => $stylist->specialization
                ];
            })
            ->sortByDesc('bookings_count')
            ->take(5);

        // 4. Status breakdown
        $statusStats = \App\Domains\Booking\Models\Booking::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // 5. Rich Visit & Traffic Analytics
        $totalPageViews = \App\Domains\Analytics\Models\VisitLog::count();
        
        $popularPages = \App\Domains\Analytics\Models\VisitLog::select('page_url', DB::raw('count(*) as count'))
            ->groupBy('page_url')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $popularSearches = \App\Domains\Analytics\Models\VisitLog::whereNotNull('search_query')
            ->select('search_query', DB::raw('count(*) as count'))
            ->groupBy('search_query')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $deviceStats = \App\Domains\Analytics\Models\VisitLog::select('device', DB::raw('count(*) as count'))
            ->groupBy('device')
            ->get()
            ->pluck('count', 'device')
            ->toArray();

        $locationStats = \App\Domains\Analytics\Models\VisitLog::select('location', DB::raw('count(*) as count'))
            ->groupBy('location')
            ->get()
            ->pluck('count', 'location')
            ->toArray();

        $genderStats = \App\Domains\Analytics\Models\VisitLog::whereNotNull('gender')
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->pluck('count', 'gender')
            ->toArray();

        $ageStats = [
            '18-25' => \App\Domains\Analytics\Models\VisitLog::whereBetween('age', [18, 25])->count(),
            '26-35' => \App\Domains\Analytics\Models\VisitLog::whereBetween('age', [26, 35])->count(),
            '36-45' => \App\Domains\Analytics\Models\VisitLog::whereBetween('age', [36, 45])->count(),
            '46+'   => \App\Domains\Analytics\Models\VisitLog::where('age', '>=', 46)->count(),
        ];

        // Access logs / audit trail
        $channelStats = \App\Domains\Analytics\Models\VisitLog::select('source_channel', DB::raw('count(*) as count'))
            ->groupBy('source_channel')
            ->get()
            ->pluck('count', 'source_channel')
            ->toArray();

        // Access logs / audit trail
        $securityLogs = \App\Domains\Analytics\Models\VisitLog::with('user')
            ->latest()
            ->take(15)
            ->get();

        $categoryStats = DB::table('service_categories')
            ->join('services', 'service_categories.id', '=', 'services.service_category_id')
            ->join('booking_items', 'services.id', '=', 'booking_items.service_id')
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id')
            ->select('service_categories.name', DB::raw('count(bookings.id) as count'), DB::raw('sum(bookings.net_amount) as revenue'))
            ->groupBy('service_categories.id', 'service_categories.name')
            ->get();

        return view('admin.analytics', compact(
            'totalBookings', 'completedBookings', 'totalRevenue', 'totalCustomers',
            'averageRating', 'outletStats', 'stylistStats', 'statusStats', 'totalPageViews',
            'popularPages', 'popularSearches', 'deviceStats', 'locationStats', 'genderStats',
            'ageStats', 'securityLogs', 'channelStats', 'categoryStats'
        ));
    }

    public function exportAnalytics(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { abort(403); }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="morehair_analytics_report_' . date('Y-m-d_H-i-s') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $logs = \App\Domains\Analytics\Models\VisitLog::with('user')->orderBy('created_at', 'desc')->get();

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(239) . chr(187) . chr(191));
            fputs($file, "sep=;\n");
            
            fputcsv($file, [
                'Waktu Akses', 'Alamat IP', 'User ID', 'Nama Pengguna', 'Halaman Dibuka', 
                'Kata Kunci Pencarian', 'Saluran Referrer / Asal Akses', 'URL Referrer Asli', 
                'Lokasi', 'Perangkat', 'Browser', 'Jenis Kelamin', 'Usia'
            ], ';');

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at,
                    $log->ip_address,
                    $log->user_id ?: '-',
                    $log->user ? $log->user->name : 'Guest',
                    $log->page_url,
                    $log->search_query ?: '-',
                    $log->source_channel ?: 'Direct',
                    $log->referrer ?: '-',
                    $log->location ?: '-',
                    $log->device ?: 'Desktop',
                    $log->browser ?: 'Other',
                    $log->gender ? ($log->gender === 'male' ? 'Laki-laki' : 'Perempuan') : '-',
                    $log->age ?: '-'
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'morehair_analytics_report_' . date('Y-m-d_H-i-s') . '.csv', $headers);
    }

    public function cms(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        
        $fields = [
            'hero_tagline_id' => \App\Domains\CMS\Services\CmsService::get('hero_tagline', 'id'),
            'hero_tagline_en' => \App\Domains\CMS\Services\CmsService::get('hero_tagline', 'en'),
            'hero_description_id' => \App\Domains\CMS\Services\CmsService::get('hero_description', 'id'),
            'hero_description_en' => \App\Domains\CMS\Services\CmsService::get('hero_description', 'en'),
            'about_tagline_id' => \App\Domains\CMS\Services\CmsService::get('about_tagline', 'id'),
            'about_tagline_en' => \App\Domains\CMS\Services\CmsService::get('about_tagline', 'en'),
            'about_description_1_id' => \App\Domains\CMS\Services\CmsService::get('about_description_1', 'id'),
            'about_description_1_en' => \App\Domains\CMS\Services\CmsService::get('about_description_1', 'en'),
            'about_description_2_id' => \App\Domains\CMS\Services\CmsService::get('about_description_2', 'id'),
            'about_description_2_en' => \App\Domains\CMS\Services\CmsService::get('about_description_2', 'en'),
            'why_title_id' => \App\Domains\CMS\Services\CmsService::get('why_title', 'id'),
            'why_title_en' => \App\Domains\CMS\Services\CmsService::get('why_title', 'en'),
            'why_subtitle_id' => \App\Domains\CMS\Services\CmsService::get('why_subtitle', 'id'),
            'why_subtitle_en' => \App\Domains\CMS\Services\CmsService::get('why_subtitle', 'en'),
            'payment_gateway_active' => \App\Domains\CMS\Services\CmsService::get('payment_gateway_active', 'id'),
        ];

        return view('admin.cms', compact('fields'));
    }

    public function updateCms(Request $request, $id = null)
    {
        if (auth()->user()->role !== 'super_admin') { abort(403); }

        \App\Domains\CMS\Services\CmsService::set('hero_tagline', ['id' => $request->hero_tagline_id, 'en' => $request->hero_tagline_en]);
        \App\Domains\CMS\Services\CmsService::set('hero_description', ['id' => $request->hero_description_id, 'en' => $request->hero_description_en]);
        \App\Domains\CMS\Services\CmsService::set('about_tagline', ['id' => $request->about_tagline_id, 'en' => $request->about_tagline_en]);
        \App\Domains\CMS\Services\CmsService::set('about_description_1', ['id' => $request->about_description_1_id, 'en' => $request->about_description_1_en]);
        \App\Domains\CMS\Services\CmsService::set('about_description_2', ['id' => $request->about_description_2_id, 'en' => $request->about_description_2_en]);
        \App\Domains\CMS\Services\CmsService::set('why_title', ['id' => $request->why_title_id, 'en' => $request->why_title_en]);
        \App\Domains\CMS\Services\CmsService::set('why_subtitle', ['id' => $request->why_subtitle_id, 'en' => $request->why_subtitle_en]);
        \App\Domains\CMS\Services\CmsService::set('payment_gateway_active', ['id' => $request->payment_gateway_active, 'en' => $request->payment_gateway_active]);

        return back()->with('message', 'Konfigurasi public berhasil disimpan!');
    }

    public function seo(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        
        $search = $request->get('search', '');
        $seoRecords = \App\Domains\SEO\Models\SEOMetadata::where('path', 'like', '%' . $search . '%')
            ->paginate(10)
            ->withQueryString();

        $editingSeo = $request->has('edit') ? \App\Domains\SEO\Models\SEOMetadata::find($request->edit) : null;
        $isCreating = $request->has('create');

        return view('admin.seo', compact('seoRecords', 'search', 'editingSeo', 'isCreating'));
    }

    public function storeSeo(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { abort(403); }

        $request->validate([
            'path' => 'required|string|min:1|max:255|unique:seo_metadata,path',
            'meta_title' => 'required|string|min:3|max:100',
            'meta_description' => 'required|string|min:5|max:255',
            'canonical_url' => 'nullable|url|max:255',
            'og_title' => 'nullable|string|max:100',
            'og_description' => 'nullable|string|max:255',
            'og_image' => 'nullable|string|max:255',
            'schema' => 'nullable|string',
        ]);

        $seo = \App\Domains\SEO\Models\SEOMetadata::create($request->all());
        return redirect()->route('admin.seo')->with('message', "SEO Metadata untuk path {$seo->path} berhasil dibuat.");
    }

    public function updateSeo(Request $request, $id)
    {
        if (auth()->user()->role !== 'super_admin') { abort(403); }

        $seo = \App\Domains\SEO\Models\SEOMetadata::findOrFail($id);

        $request->validate([
            'path' => 'required|string|min:1|max:255|unique:seo_metadata,path,' . $id,
            'meta_title' => 'required|string|min:3|max:100',
            'meta_description' => 'required|string|min:5|max:255',
            'canonical_url' => 'nullable|url|max:255',
            'og_title' => 'nullable|string|max:100',
            'og_description' => 'nullable|string|max:255',
            'og_image' => 'nullable|string|max:255',
            'schema' => 'nullable|string',
        ]);

        $seo->update($request->all());
        return redirect()->route('admin.seo')->with('message', "SEO Metadata untuk path {$seo->path} berhasil diperbarui.");
    }

    public function deleteSeo($id)
    {
        if (auth()->user()->role !== 'super_admin') { abort(403); }

        $seo = \App\Domains\SEO\Models\SEOMetadata::findOrFail($id);
        $seo->delete();
        return redirect()->route('admin.seo')->with('message', "Data SEO untuk path {$seo->path} berhasil dihapus.");
    }

    public function settings(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { return redirect()->route('dashboard'); }
        
        $activeTab = $request->get('tab', 'general');
        
        $query = \App\Domains\System\Models\Setting::query();
        if ($activeTab === 'whatsapp') {
            $query->where('key', 'like', 'whatsapp.%')->where('group', '!=', 'whatsapp_notifications');
        } elseif ($activeTab === 'whatsapp_notifications') {
            $query->where('group', 'whatsapp_notifications');
        } else {
            $query->where('group', $activeTab);
        }
        
        $settingsMeta = $query->orderBy('id', 'asc')->get();

        $settingsData = [];
        foreach (\App\Domains\System\Models\Setting::all() as $s) {
            $settingsData[$s->key] = $s->value;
        }

        return view('admin.settings', compact('settingsMeta', 'settingsData', 'activeTab'));
    }

    public function updateSettings(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') { abort(403); }

        $activeTab = $request->get('tab', 'general');
        $inputs = $request->except(['_token', 'tab']);

        // Manual validation checks
        if ($activeTab === 'general') {
            $request->validate([
                'app_name' => 'nullable|string', // mapping to app.name
                'app_url' => 'nullable|url'     // mapping to app.url
            ]);
        } elseif ($activeTab === 'whatsapp') {
            $provider = $request->get('whatsapp_provider', 'meta');
            if ($provider === 'meta') {
                $request->validate([
                    'whatsapp_meta_token' => 'required|string',
                    'whatsapp_meta_phone_number_id' => 'required|string',
                    'whatsapp_meta_version' => 'required|string'
                ]);
            } else {
                $request->validate([
                    'whatsapp_fonnte_token' => 'required|string'
                ]);
            }
        } elseif ($activeTab === 'payment') {
            $request->validate([
                'services_midtrans_server_key' => 'required|string',
                'services_midtrans_client_key' => 'required|string'
            ]);
        }

        // Save settings mapping keys back (replace underscore with dot in incoming keys)
        $settings = \App\Domains\System\Models\Setting::all();
        foreach ($settings as $setting) {
            $isTargetSetting = false;

            if ($activeTab === 'general' && $setting->group === 'general') {
                $isTargetSetting = true;
            } elseif ($activeTab === 'whatsapp' && str_starts_with($setting->key, 'whatsapp.') && $setting->group !== 'whatsapp_notifications') {
                $isTargetSetting = true;
            } elseif ($activeTab === 'whatsapp_notifications' && $setting->group === 'whatsapp_notifications') {
                $isTargetSetting = true;
            } elseif ($activeTab === 'payment' && $setting->group === 'payment') {
                $isTargetSetting = true;
            }

            if ($isTargetSetting) {
                // Convert setting key (dot notation) to field parameter (underscore notation)
                $fieldKey = str_replace('.', '_', $setting->key);
                
                if ($setting->type === 'boolean') {
                    $value = $request->has($fieldKey) ? 'true' : 'false';
                } else {
                    $value = $request->get($fieldKey, $setting->value);
                }

                $setting->update(['value' => $value]);
                config([$setting->key => $setting->casted_value]);
            }
        }

        return redirect()->route('admin.settings', ['tab' => $activeTab])
            ->with('message', 'Konfigurasi sistem berhasil diperbarui secara dinamis!');
    }

    public function transactions(Request $request)
    {
        Gate::authorize('pos.view');
        
        $search = $request->get('search', '');
        $filterOutlet = $request->get('filterOutlet', '');
        $filterPaymentMethod = $request->get('filterPaymentMethod', '');
        $dateFrom = $request->get('dateFrom', '');
        $dateTo = $request->get('dateTo', '');

        if (auth()->user()->role === 'outlet_admin') {
            $filterOutlet = auth()->user()->outlet_id;
        }

        $query = PosTransaction::with(['customer', 'outlet']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function ($c) use ($search) {
                      $c->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($filterOutlet) {
            $query->where('outlet_id', $filterOutlet);
        }

        if ($filterPaymentMethod) {
            $query->where('payment_method', $filterPaymentMethod);
        }

        if ($dateFrom) {
            $query->whereDate('completed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('completed_at', '<=', $dateTo);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();
        $outlets = Outlet::all();

        $selectedTransaction = null;
        if ($request->has('view_tx')) {
            $selectedTransaction = PosTransaction::with(['customer', 'outlet', 'items.service', 'items.product', 'stylist'])
                ->find($request->view_tx);
        }

        return view('admin.transactions', compact(
            'transactions', 'outlets', 'search', 'filterOutlet', 'filterPaymentMethod',
            'dateFrom', 'dateTo', 'selectedTransaction'
        ));
    }

    public function refundTransaction($id)
    {
        if (auth()->user()->role !== 'super_admin') { abort(403); }

        try {
            $transaction = PosTransaction::findOrFail($id);
            POSTransactionService::refund($transaction->id);
            return back()->with('message', 'Transaksi berhasil di-refund.');
        } catch (\Exception $e) {
            return back()->with('error', 'Refund gagal: ' . $e->getMessage());
        }
    }
}
