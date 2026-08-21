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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

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
        $stylists = Stylist::with('outlet')
            ->where('name', 'like', '%' . $search . '%')
            ->paginate(10)
            ->withQueryString();

        $outlets = Outlet::all();
        $users = User::where('role', 'stylist')->get();
        $editingStylist = $request->has('edit') ? Stylist::find($request->edit) : null;
        $isCreating = $request->has('create');

        return view('admin.stylists', compact('stylists', 'search', 'outlets', 'users', 'editingStylist', 'isCreating'));
    }

    public function storeStylist(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stylists,slug',
            'outlet_id' => 'required|exists:outlets,id',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string',
            'status' => 'required|in:active,inactive,pending_active,pending_inactive'
        ]);

        Stylist::create($request->all());
        return redirect()->route('admin.stylists')->with('message', 'Stylist berhasil ditambahkan.');
    }

    public function updateStylist(Request $request, $id)
    {
        $stylist = Stylist::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stylists,slug,' . $id,
            'outlet_id' => 'required|exists:outlets,id',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string',
            'status' => 'required|in:active,inactive,pending_active,pending_inactive'
        ]);

        $stylist->update($request->all());
        return redirect()->route('admin.stylists')->with('message', 'Stylist berhasil diperbarui.');
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
        Customer::destroy($id);
        return redirect()->route('admin.customers')->with('message', 'Pelanggan berhasil dihapus.');
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

        $allRawCustomers = $query->get();

        // Hydrate RFM segments in collection
        $customerList = $allRawCustomers->map(function ($c) {
            $rfm = \App\Domains\CRM\Services\RFMService::analyze($c);
            $behavior = \App\Domains\CRM\Services\CRMAnalyticsService::getBehavior($c);
            $c->rfm_segment = $rfm['segment'];
            $c->rfm_score = $rfm['rfm_code'];
            $c->total_spending = $rfm['total_spending'];
            $c->total_visits = $rfm['total_visits'];
            $c->retention_status = $behavior['retention_status'];
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

        // 2. Outlet breakdown (Revenue & Bookings count)
        $outletStats = Outlet::withCount('bookings')
            ->get()
            ->map(function ($outlet) {
                $revenue = \App\Domains\Booking\Models\Booking::where('outlet_id', $outlet->id)
                    ->whereIn('status', ['completed', 'checked_in', 'in_progress'])
                    ->sum('net_amount');
                return [
                    'name' => $outlet->name,
                    'bookings_count' => $outlet->bookings_count,
                    'revenue' => $revenue
                ];
            });

        // 3. Stylist rating & bookings breakdown
        $stylistStats = Stylist::withCount('bookings')
            ->get()
            ->map(function ($stylist) {
                $rating = \App\Domains\Review\Models\Review::whereHas('booking', function ($q) use ($stylist) {
                    $q->where('stylist_id', $stylist->id);
                })->avg('rating') ?: $stylist->rating;
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
