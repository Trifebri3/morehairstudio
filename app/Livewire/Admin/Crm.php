<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\Customer\Models\Customer;
use App\Domains\Booking\Models\Booking;
use App\Domains\POS\Models\PosTransaction;
use App\Domains\CRM\Services\RFMService;
use App\Domains\CRM\Services\CRMAnalyticsService;
use App\Domains\System\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Crm extends Component
{
    // Filter parameters
    public $filterOutlet = '';
    public $filterSource = '';
    public $filterSegment = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Search and detail
    public $search = '';
    public $selectedCustomerId = null;

    protected $queryString = [
        'filterOutlet' => ['except' => ''],
        'filterSource' => ['except' => ''],
        'filterSegment' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function selectCustomer($id)
    {
        $this->selectedCustomerId = $id;
    }

    /**
     * Download CRM dataset based on current active filters.
     */
    public function exportExcel()
    {
        Gate::authorize('customer.export');

        // Capture filtered query
        $query = Customer::query();

        // Enforce Multi-outlet isolation if logged user is outlet_admin
        if (auth()->user()->role === 'outlet_admin') {
            $this->filterOutlet = auth()->user()->outlet_id;
        }

        if ($this->filterOutlet) {
            $query->where(function ($q) {
                $q->whereHas('bookings', function ($b) {
                    $b->where('outlet_id', $this->filterOutlet);
                })->orWhereHas('posTransactions', function ($p) {
                    $p->where('outlet_id', $this->filterOutlet);
                });
            });
        }

        if ($this->filterSource) {
            $query->where('first_acquisition_source', $this->filterSource);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $customers = $query->get();

        // Apply dynamic segment filtering in memory since RFM segments are calculated
        if ($this->filterSegment) {
            $customers = $customers->filter(function ($c) {
                $rfm = RFMService::analyze($c);
                return strtolower($rfm['segment']) === strtolower($this->filterSegment);
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

        // Audit the export action
        AuditLogger::log('customer.export', null, null, null, ['record_count' => $customers->count()]);

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fputs($file, chr(239) . chr(187) . chr(191));
            
            // Instruct Microsoft Excel to use semicolon separator
            fputs($file, "sep=;\n");

            // Write Headers
            fputcsv($file, [
                'ID Pelanggan',
                'Kode Pelanggan',
                'Nama',
                'Telepon',
                'WhatsApp',
                'Email',
                'Gender',
                'Tanggal Lahir',
                'Loyalty Points',
                'Sumber Pertama',
                'Sumber Terbaru',
                'Kunjungan Pertama',
                'Kunjungan Terakhir',
                'Total Kunjungan',
                'Total Belanja (IDR)',
                'Rata-rata Belanja (IDR)',
                'Segmen RFM',
                'Status',
                'Tanggal Daftar'
            ], ';');

            foreach ($customers as $c) {
                $rfm = RFMService::analyze($c);
                $behavior = CRMAnalyticsService::getBehavior($c);

                fputcsv($file, [
                    $c->id,
                    $c->customer_code,
                    $c->name,
                    $c->phone,
                    $c->whatsapp_phone ?: '-',
                    $c->email ?: '-',
                    $c->gender ?: '-',
                    $c->birth_date ? $c->birth_date->toDateString() : '-',
                    $c->loyalty_points,
                    $c->first_acquisition_source,
                    $c->latest_acquisition_source,
                    $behavior['first_visit'] ?: '-',
                    $behavior['last_visit'] ?: '-',
                    $behavior['total_visits'],
                    $behavior['total_spending'],
                    $behavior['average_spending'],
                    $rfm['segment'],
                    $c->status,
                    $c->created_at->toDateTimeString()
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    public function render()
    {
        Gate::authorize('customer.view');

        // Apply Multi-outlet filter isolation
        if (auth()->user()->role === 'outlet_admin') {
            $this->filterOutlet = auth()->user()->outlet_id;
        }

        // Fetch customer list with search/filter
        $query = Customer::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterOutlet) {
            $query->where(function ($q) {
                $q->whereHas('bookings', function ($b) {
                    $b->where('outlet_id', $this->filterOutlet);
                })->orWhereHas('posTransactions', function ($p) {
                    $p->where('outlet_id', $this->filterOutlet);
                });
            });
        }

        if ($this->filterSource) {
            $query->where('first_acquisition_source', $this->filterSource);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $allRawCustomers = $query->get();

        // Hydrate RFM segments in collection
        $customerList = $allRawCustomers->map(function ($c) {
            $rfm = RFMService::analyze($c);
            $behavior = CRMAnalyticsService::getBehavior($c);
            $c->rfm_segment = $rfm['segment'];
            $c->rfm_score = $rfm['rfm_code'];
            $c->total_spending = $rfm['total_spending'];
            $c->total_visits = $rfm['total_visits'];
            $c->retention_status = $behavior['retention_status'];
            return $c;
        });

        // Filter segment in collection (calculated value)
        if ($this->filterSegment) {
            $customerList = $customerList->filter(function ($c) {
                return strtolower($c->rfm_segment) === strtolower($this->filterSegment);
            });
        }

        // Compute dashboard metrics
        $totalCustomers = $customerList->count();
        $newCustomers = $customerList->filter(function ($c) {
            return $c->created_at->gte(Carbon::now()->subDays(30));
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
        $selectedCustomer = null;
        $timeline = [];
        if ($this->selectedCustomerId) {
            $selectedCustomer = Customer::findOrFail($this->selectedCustomerId);
            
            // Get behavioral data
            $selectedCustomer->behavior = CRMAnalyticsService::getBehavior($selectedCustomer);
            $selectedCustomer->rfm = RFMService::analyze($selectedCustomer);

            // Fetch customer timeline activities
            $timeline = $selectedCustomer->activities()->orderBy('event_date', 'desc')->get();
        }

        $outlets = \App\Domains\Outlet\Models\Outlet::all();

        return view('livewire.admin.crm', compact(
            'customerList', 'totalCustomers', 'newCustomers', 'activeCount',
            'inactiveCount', 'atRiskCount', 'lostCount', 'repeatRate', 'avgSpending',
            'retentionRate', 'segmentBreakdown', 'sourceBreakdown', 'selectedCustomer',
            'timeline', 'outlets'
        ))->layout('layouts.admin');
    }
}
