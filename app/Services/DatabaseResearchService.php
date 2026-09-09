<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseResearchService
{
    /**
     * Get overall database technical overview, storage sizes, and key table row counts.
     */
    public function getDatabaseOverview(): array
    {
        $dbName = config('database.connections.mysql.database');

        // Database size in MB
        $sizeResult = DB::select("
            SELECT table_schema AS 'database_name',
                   ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb',
                   COUNT(table_name) AS 'total_tables'
            FROM information_schema.tables
            WHERE table_schema = ?
            GROUP BY table_schema
        ", [$dbName]);

        $dbSizeMb = $sizeResult[0]->size_mb ?? 0;
        $totalTables = $sizeResult[0]->total_tables ?? 0;

        // Key tables statistics
        $keyTables = [
            'customers' => 'Data Pelanggan',
            'bookings' => 'Data Reservasi / Pemesanan',
            'booking_items' => 'Item Layanan Pemesanan',
            'payments' => 'Transaksi & Pembayaran',
            'stylists' => 'Data Kapster / Barber',
            'stylist_schedules' => 'Jadwal Kerja Stylist',
            'services' => 'Katalog Layanan',
            'service_categories' => 'Kategori Layanan',
            'outlets' => 'Data Outlet / Cabang',
            'promotions' => 'Promo & Kupon Diskon',
            'reviews' => 'Ulasan & Rating Pelanggan',
            'attendances' => 'Data Presensi Kapster',
            'audit_logs' => 'Audit Log Sistem',
            'whatsapp_messages' => 'Pesan WhatsApp CRM'
        ];

        $tableStats = [];
        foreach ($keyTables as $tbl => $label) {
            $count = 0;
            try {
                $count = DB::table($tbl)->count();
            } catch (\Exception $e) {
                $count = 0;
            }
            $tableStats[] = [
                'table' => $tbl,
                'label' => $label,
                'count' => $count
            ];
        }

        return [
            'database_name' => $dbName,
            'size_mb' => $dbSizeMb,
            'total_tables' => $totalTables,
            'table_stats' => $tableStats
        ];
    }

    /**
     * Customer intelligence: demographics, new vs returning retention, top spenders.
     */
    public function getCustomerAnalytics(): array
    {
        $totalCustomers = DB::table('customers')->count();

        // Customer retention: repeat customers are those who have > 1 non-cancelled/expired bookings
        $repeatCustomerIds = DB::table('bookings')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('count(*) > 1')
            ->pluck('customer_id')
            ->toArray();

        $repeatCustomers = count($repeatCustomerIds);
        $newCustomers = max(0, $totalCustomers - $repeatCustomers);
        $retentionRate = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 1) : 0;

        // Top 10 High-Value VIP Customers (Customer Lifetime Value)
        $topSpenders = DB::table('customers')
            ->join('bookings', function($join) {
                $join->on('customers.id', '=', 'bookings.customer_id')
                     ->whereNotIn('bookings.status', ['cancelled', 'expired']);
            })
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.customer_code',
                DB::raw('COUNT(DISTINCT bookings.id) as total_bookings'),
                DB::raw('COALESCE(SUM(bookings.net_amount), 0) as lifetime_spent'),
                DB::raw('MAX(bookings.booking_date) as last_visit')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.customer_code')
            ->orderByDesc('lifetime_spent')
            ->limit(10)
            ->get();

        // Gender breakdown
        $genderBreakdown = DB::table('customers')
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        return [
            'total_customers' => $totalCustomers,
            'repeat_customers' => $repeatCustomers,
            'new_customers' => $newCustomers,
            'retention_rate' => $retentionRate,
            'top_spenders' => $topSpenders,
            'gender_breakdown' => $genderBreakdown
        ];
    }

    /**
     * Booking & capacity analytics: peak hours, peak days, status ratios, walk-in vs web.
     */
    public function getBookingAnalytics(): array
    {
        $totalBookings = DB::table('bookings')->count();

        // Status breakdown
        $statusCounts = DB::table('bookings')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Source breakdown (website vs walk_in)
        $sourceCounts = DB::table('bookings')
            ->select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        // Peak hours analysis (group by hour of start_time from booking_items)
        $peakHours = DB::table('booking_items')
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id')
            ->whereNotIn('bookings.status', ['cancelled', 'expired'])
            ->select(DB::raw('SUBSTRING(booking_items.start_time, 1, 2) as hour_start'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('SUBSTRING(booking_items.start_time, 1, 2)'))
            ->orderBy('hour_start')
            ->get()
            ->mapWithKeys(function ($item) {
                return [(int)$item->hour_start . ':00' => $item->total];
            })
            ->toArray();

        // Peak days of week
        $peakDays = DB::table('bookings')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->select(DB::raw('DAYNAME(booking_date) as day_name'), DB::raw('DAYOFWEEK(booking_date) as day_num'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DAYNAME(booking_date)'), DB::raw('DAYOFWEEK(booking_date)'))
            ->orderBy('day_num')
            ->pluck('total', 'day_name')
            ->toArray();

        // Cancellation & No-Show rate
        $cancelledCount = ($statusCounts['cancelled'] ?? 0) + ($statusCounts['expired'] ?? 0);
        $cancellationRate = $totalBookings > 0 ? round(($cancelledCount / $totalBookings) * 100, 1) : 0;

        return [
            'total_bookings' => $totalBookings,
            'status_counts' => $statusCounts,
            'source_counts' => $sourceCounts,
            'peak_hours' => $peakHours,
            'peak_days' => $peakDays,
            'cancellation_rate' => $cancellationRate
        ];
    }

    /**
     * Financial & revenue intelligence: total revenue, AOV, payment distribution.
     */
    public function getFinancialAnalytics(): array
    {
        $totalRevenue = DB::table('bookings')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->sum('net_amount');

        $totalCompletedBookings = DB::table('bookings')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->count();

        $averageOrderValue = $totalCompletedBookings > 0 ? round($totalRevenue / $totalCompletedBookings) : 0;

        // Payment method distribution
        $paymentMethods = DB::table('payments')
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get();

        // Total discount claimed
        $totalDiscount = DB::table('bookings')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->sum(DB::raw('GREATEST(0, total_amount - net_amount)'));

        // Monthly revenue trend (last 6 months)
        $monthlyRevenue = DB::table('bookings')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->where('booking_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(booking_date, '%Y-%m') as ym"),
                DB::raw("DATE_FORMAT(booking_date, '%b %Y') as label"),
                DB::raw('SUM(net_amount) as total_revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('ym', 'label')
            ->orderBy('ym')
            ->get();

        return [
            'total_revenue' => (float)$totalRevenue,
            'average_order_value' => (float)$averageOrderValue,
            'payment_methods' => $paymentMethods,
            'total_discount' => (float)$totalDiscount,
            'monthly_revenue' => $monthlyRevenue
        ];
    }

    /**
     * Stylist & Barber performance analytics: client volume, revenue contribution, reviews.
     */
    public function getStylistAnalytics(): array
    {
        return DB::table('stylists')
            ->leftJoin('bookings', function ($join) {
                $join->on('stylists.id', '=', 'bookings.stylist_id')
                     ->whereNotIn('bookings.status', ['cancelled', 'expired']);
            })
            ->leftJoin('reviews', 'stylists.id', '=', 'reviews.stylist_id')
            ->select(
                'stylists.id',
                'stylists.name',
                'stylists.specialization',
                'stylists.status',
                DB::raw('COUNT(DISTINCT bookings.id) as total_clients_served'),
                DB::raw('COALESCE(SUM(bookings.net_amount), 0) as total_revenue_generated'),
                DB::raw('ROUND(AVG(reviews.rating), 1) as avg_rating'),
                DB::raw('COUNT(DISTINCT reviews.id) as total_reviews')
            )
            ->groupBy('stylists.id', 'stylists.name', 'stylists.specialization', 'stylists.status')
            ->orderByDesc('total_clients_served')
            ->get()
            ->toArray();
    }

    /**
     * Service catalogue analytics: most booked haircuts and grooming packages.
     */
    public function getServiceAnalytics(): array
    {
        return DB::table('services')
            ->leftJoin('booking_items', 'services.id', '=', 'booking_items.service_id')
            ->leftJoin('bookings', function($join) {
                $join->on('booking_items.booking_id', '=', 'bookings.id')
                     ->whereNotIn('bookings.status', ['cancelled', 'expired']);
            })
            ->leftJoin('service_categories', 'services.service_category_id', '=', 'service_categories.id')
            ->select(
                'services.id',
                'services.name',
                'service_categories.name as category_name',
                'services.default_price',
                'services.default_duration',
                DB::raw('COUNT(booking_items.id) as total_booked_count'),
                DB::raw('COALESCE(SUM(booking_items.price), 0) as total_revenue')
            )
            ->groupBy('services.id', 'services.name', 'service_categories.name', 'services.default_price', 'services.default_duration')
            ->orderByDesc('total_booked_count')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Safe Read-Only SQL Query Executor for Administrator Data Exploration.
     */
    public function executeReadOnlyQuery(string $sql): array
    {
        $cleanSql = trim($sql);

        // Security Validation: Must be SELECT statement only
        if (!preg_match('/^SELECT\s+/i', $cleanSql)) {
            return [
                'success' => false,
                'message' => 'Hanya kueri SELECT yang diizinkan untuk riset data demi keamanan database.',
                'columns' => [],
                'rows' => [],
                'duration_ms' => 0
            ];
        }

        // Dangerous keywords prevention
        $forbiddenKeywords = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'TRUNCATE', 'RENAME', 'GRANT', 'REVOKE', 'REPLACE', 'EXEC', 'EXECUTE', 'INTO OUTFILE', 'INTO DUMPFILE'];
        foreach ($forbiddenKeywords as $keyword) {
            if (preg_match('/\b' . $keyword . '\b/i', $cleanSql)) {
                return [
                    'success' => false,
                    'message' => "Instruksi {$keyword} dilarang dalam modul riset database.",
                    'columns' => [],
                    'rows' => [],
                    'duration_ms' => 0
                ];
            }
        }

        // Auto-limit to 100 rows if no LIMIT specified
        if (!preg_match('/\bLIMIT\s+\d+/i', $cleanSql)) {
            $cleanSql = rtrim($cleanSql, ';') . ' LIMIT 100;';
        }

        $startTime = microtime(true);
        try {
            $results = DB::select($cleanSql);
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $rows = array_map(fn($item) => (array)$item, $results);
            $columns = !empty($rows) ? array_keys($rows[0]) : [];

            return [
                'success' => true,
                'message' => 'Kueri berhasil dieksekusi dalam ' . $durationMs . ' ms (' . count($rows) . ' baris ditemukan).',
                'columns' => $columns,
                'rows' => $rows,
                'duration_ms' => $durationMs
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Kesalahan sintaks SQL: ' . $e->getMessage(),
                'columns' => [],
                'rows' => [],
                'duration_ms' => 0
            ];
        }
    }

    /**
     * Preset SQL research queries for quick executive analysis.
     */
    public function getPresetQueries(): array
    {
        return [
            [
                'id' => 'top_spenders',
                'title' => 'Top 10 Pelanggan VIP (Customer Lifetime Value)',
                'description' => 'Mengekstrak pelanggan dengan akumulasi transaksi tertinggi di MORE Hair Studio.',
                'sql' => "SELECT c.customer_code, c.name, c.phone, COUNT(b.id) AS total_kunjungan, FORMAT(SUM(b.net_amount), 0) AS total_pengeluaran_rp, MAX(b.booking_date) AS kunjungan_terakhir FROM customers c JOIN bookings b ON c.id = b.customer_id WHERE b.status NOT IN ('cancelled', 'expired') GROUP BY c.id, c.customer_code, c.name, c.phone ORDER BY SUM(b.net_amount) DESC LIMIT 10;"
            ],
            [
                'id' => 'peak_hours',
                'title' => 'Distribusi Jam Kedatangan Paling Ramai (Peak Hours)',
                'description' => 'Menganalisis jam berapa pelanggan paling sering melakukan treatment.',
                'sql' => "SELECT SUBSTRING(bi.start_time, 1, 2) AS jam_mulai, COUNT(*) AS total_booking FROM booking_items bi JOIN bookings b ON bi.booking_id = b.id WHERE b.status NOT IN ('cancelled', 'expired') GROUP BY SUBSTRING(bi.start_time, 1, 2) ORDER BY total_booking DESC;"
            ],
            [
                'id' => 'stylist_performance',
                'title' => 'Rangkuman Performa & Omzet per Stylist / Kapster',
                'description' => 'Mengevaluasi kontribusi omzet dan total customer yang dilayani masing-masing barber.',
                'sql' => "SELECT s.name AS nama_stylist, s.specialization, COUNT(b.id) AS total_klien, FORMAT(COALESCE(SUM(b.net_amount), 0), 0) AS omzet_dihasilkan_rp, ROUND(AVG(r.rating), 1) AS rating_rata_rata FROM stylists s LEFT JOIN bookings b ON s.id = b.stylist_id AND b.status NOT IN ('cancelled', 'expired') LEFT JOIN reviews r ON s.id = r.stylist_id GROUP BY s.id, s.name, s.specialization ORDER BY SUM(b.net_amount) DESC;"
            ],
            [
                'id' => 'popular_services',
                'title' => '10 Layanan Paling Banyak Dipesan (Service Popularity)',
                'description' => 'Melihat layanan haircut, grooming, atau treatment yang paling digemari pelanggan.',
                'sql' => "SELECT s.name AS nama_layanan, sc.name AS kategori, s.default_price AS harga_standar, COUNT(bi.id) AS frekuensi_dipesan, FORMAT(COALESCE(SUM(bi.price), 0), 0) AS total_pendapatan_rp FROM services s JOIN service_categories sc ON s.service_category_id = sc.id LEFT JOIN booking_items bi ON s.id = bi.service_id GROUP BY s.id, s.name, sc.name, s.default_price ORDER BY frekuensi_dipesan DESC LIMIT 10;"
            ],
            [
                'id' => 'churn_risk',
                'title' => 'Pelanggan Belum Kembali Lebih dari 45 Hari (Churn Risk)',
                'description' => 'Mengidentifikasi pelanggan setia yang sudah lama tidak berkunjung untuk retensi CRM.',
                'sql' => "SELECT c.customer_code, c.name, c.phone, MAX(b.booking_date) AS kunjungan_terakhir, DATEDIFF(CURRENT_DATE, MAX(b.booking_date)) AS hari_sejak_kunjungan_terakhir FROM customers c JOIN bookings b ON c.id = b.customer_id WHERE b.status NOT IN ('cancelled', 'expired') GROUP BY c.id, c.customer_code, c.name, c.phone HAVING MAX(b.booking_date) <= DATE_SUB(CURRENT_DATE, INTERVAL 45 DAY) ORDER BY hari_sejak_kunjungan_terakhir DESC LIMIT 20;"
            ]
        ];
    }
}
