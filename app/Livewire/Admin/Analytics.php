<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\Booking\Models\Booking;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Review\Models\Review;
use Illuminate\Support\Facades\DB;

class Analytics extends Component
{
    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        // 1. High-level totals
        $totalBookings = Booking::count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $totalRevenue = Booking::whereIn('status', ['completed', 'checked_in', 'in_progress'])
            ->sum('net_amount');
        $totalCustomers = Customer::count();
        $averageRating = Review::avg('rating') ?: 5.0;

        // 2. Outlet breakdown (Revenue & Bookings count)
        $outletStats = Outlet::withCount('bookings')
            ->get()
            ->map(function ($outlet) {
                $revenue = Booking::where('outlet_id', $outlet->id)
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
                $rating = Review::whereHas('booking', function ($q) use ($stylist) {
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
        $statusStats = Booking::select('status', DB::raw('count(*) as count'))
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

        return view('livewire.admin.analytics', compact(
            'totalBookings',
            'completedBookings',
            'totalRevenue',
            'totalCustomers',
            'averageRating',
            'outletStats',
            'stylistStats',
            'statusStats',
            'totalPageViews',
            'popularPages',
            'popularSearches',
            'deviceStats',
            'locationStats',
            'genderStats',
            'ageStats',
            'securityLogs',
            'channelStats',
            'categoryStats'
        ))->layout('layouts.admin');
    }

    public function exportToExcel()
    {
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
            
            // Add UTF-8 BOM
            fputs($file, chr(239) . chr(187) . chr(191));
            
            // Instruct Microsoft Excel to use semicolon separator
            fputs($file, "sep=;\n");
            
            // CSV Headers
            fputcsv($file, [
                'Waktu Akses', 
                'Alamat IP', 
                'User ID', 
                'Nama Pengguna', 
                'Halaman Dibuka', 
                'Kata Kunci Pencarian', 
                'Saluran Referrer / Asal Akses', 
                'URL Referrer Asli', 
                'Lokasi', 
                'Perangkat', 
                'Browser', 
                'Jenis Kelamin', 
                'Usia'
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
}
