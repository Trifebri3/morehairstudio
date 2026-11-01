<?php

namespace App\Domains\CRM\Services;

use App\Domains\Customer\Models\Customer;
use App\Domains\Booking\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CRMAnalyticsService
{
    /**
     * Compute comprehensive derived customer behavior analytics.
     */
    public static function getBehavior(Customer $customer): array
    {
        // 1. Visit logs (check if relations are loaded to eliminate N+1 queries)
        $allBookings = $customer->relationLoaded('bookings') ? $customer->bookings : $customer->bookings()->get();
        $allPosTxs = $customer->relationLoaded('posTransactions') ? $customer->posTransactions : $customer->posTransactions()->get();

        $bookings = $allBookings->filter(fn($b) => $b->status === 'completed')->sortBy('booking_date')->values();
        $posTxs = $allPosTxs->filter(fn($tx) => $tx->status === 'completed')->sortBy('completed_at')->values();

        $allVisits = collect();
        foreach ($bookings as $b) {
            $allVisits->push(Carbon::parse($b->booking_date));
        }
        foreach ($posTxs as $tx) {
            $allVisits->push(Carbon::parse($tx->completed_at));
        }
        $allVisits = $allVisits->sort()->values();

        $firstVisit = $allVisits->first();
        $lastVisit = $allVisits->last();
        $totalVisits = $allVisits->count();

        // Calculate average days between visits
        $avgDaysBetween = 0;
        if ($totalVisits > 1) {
            $diffs = [];
            for ($i = 1; $i < $totalVisits; $i++) {
                $diffs[] = $allVisits[$i]->diffInDays($allVisits[$i - 1]);
            }
            $avgDaysBetween = round(array_sum($diffs) / count($diffs));
        }

        $daysSinceLast = $lastVisit ? Carbon::now()->diffInDays($lastVisit) : 999;

        // 2. Spending
        $bookingSpend = (float)$bookings->sum('net_amount');
        $txSpend = (float)$posTxs->sum('grand_total');
        $totalSpending = $bookingSpend + $txSpend;

        $averageSpending = $totalVisits > 0 ? round($totalSpending / $totalVisits, 2) : 0;

        // Highest transaction
        $highestBooking = (float)$bookings->max('net_amount') ?: 0;
        $highestTx = (float)$posTxs->max('grand_total') ?: 0;
        $highestSpending = max($highestBooking, $highestTx);

        // 3. Booking Stats
        $totalBookings = $allBookings->count();
        $completedBookings = $bookings->count();
        $cancelledBookings = $allBookings->filter(fn($b) => $b->status === 'cancelled')->count();
        $noShowBookings = $allBookings->filter(fn($b) => $b->status === 'no_show')->count();

        $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100) : 0;
        $cancellationRate = $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100) : 0;
        $noShowRate = $totalBookings > 0 ? round(($noShowBookings / $totalBookings) * 100) : 0;

        // 4. Preferences (Favorite outlet, barber/stylist, and service)
        // Favorite Outlet
        $favOutlet = DB::table('bookings')
            ->select('outlet_id', DB::raw('count(*) as count'))
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->groupBy('outlet_id')
            ->orderBy('count', 'desc')
            ->first();
        $favoriteOutlet = $favOutlet ? \App\Domains\Outlet\Models\Outlet::find($favOutlet->outlet_id) : null;

        // Favorite Stylist / Barber
        $favStylist = DB::table('bookings')
            ->select('stylist_id', DB::raw('count(*) as count'))
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->whereNotNull('stylist_id')
            ->groupBy('stylist_id')
            ->orderBy('count', 'desc')
            ->first();
        $favoriteStylist = $favStylist ? \App\Domains\Stylist\Models\Stylist::find($favStylist->stylist_id) : null;

        // Favorite Service
        $favService = DB::table('booking_items')
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id')
            ->select('booking_items.service_id', DB::raw('count(*) as count'))
            ->where('bookings.customer_id', $customer->id)
            ->where('bookings.status', 'completed')
            ->groupBy('booking_items.service_id')
            ->orderBy('count', 'desc')
            ->first();
        $favoriteService = $favService ? \App\Domains\Service\Models\Service::find($favService->service_id) : null;

        // 5. Retention Status
        $retentionStatus = 'Active';
        if ($daysSinceLast > 180) {
            $retentionStatus = 'Lost';
        } elseif ($daysSinceLast > 90) {
            $retentionStatus = 'At Risk';
        } elseif ($daysSinceLast > 45) {
            $retentionStatus = 'Inactive';
        }

        return [
            'first_visit' => $firstVisit ? $firstVisit->toDateString() : null,
            'last_visit' => $lastVisit ? $lastVisit->toDateString() : null,
            'total_visits' => $totalVisits,
            'average_days_between' => $avgDaysBetween,
            'days_since_last' => $daysSinceLast,
            'total_spending' => $totalSpending,
            'average_spending' => $averageSpending,
            'highest_spending' => $highestSpending,
            'total_bookings' => $totalBookings,
            'completed_bookings' => $completedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'no_show_bookings' => $noShowBookings,
            'completion_rate' => $completionRate,
            'cancellation_rate' => $cancellationRate,
            'no_show_rate' => $noShowRate,
            'favorite_outlet' => $favoriteOutlet ? $favoriteOutlet->name : '-',
            'favorite_stylist' => $favoriteStylist ? $favoriteStylist->name : '-',
            'favorite_service' => $favoriteService ? $favoriteService->name : '-',
            'retention_status' => $retentionStatus
        ];
    }

    /**
     * Compute ranked referral source intelligence (Darimana Tahu MORE).
     * Automatically ranks sources from highest to lowest count.
     */
    public static function getReferralSourceRankings(): array
    {
        // 1. Get raw booking referral counts
        $bookingCounts = [];
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'referral_source')) {
            $bookingCounts = Booking::whereNotNull('referral_source')
                ->where('referral_source', '!=', '')
                ->select('referral_source', DB::raw('count(*) as count'))
                ->groupBy('referral_source')
                ->pluck('count', 'referral_source')
                ->toArray();
        }

        // 2. Get customer acquisition sources
        $customerCounts = [];
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'first_acquisition_source')) {
            $customerCounts = Customer::whereNotNull('first_acquisition_source')
                ->where('first_acquisition_source', '!=', '')
                ->select('first_acquisition_source', DB::raw('count(*) as count'))
                ->groupBy('first_acquisition_source')
                ->pluck('count', 'first_acquisition_source')
                ->toArray();
        }

        // 3. Normalize & group
        $grouped = [];
        $details = []; // holds custom "Lainnya: ..." specific text

        $normalize = function ($str) {
            $s = trim($str);
            if (preg_match('/^ig\b|^instagram/i', $s)) {
                if (stripos($s, 'capter') !== false) return 'IG Capters';
                return 'Instagram';
            }
            if (preg_match('/^wa\b|^whatsapp/i', $s)) return 'WhatsApp';
            if (stripos($s, 'capter') !== false) return 'IG Capters';
            if (preg_match('/kabar|teman|mulut|rekomendasi|friend/i', $s)) return 'Kabar / Teman';
            if (stripos($s, 'tiktok') !== false) return 'TikTok';
            if (stripos($s, 'google') !== false) return 'Google';
            if (stripos($s, 'lainnya') === 0 || stripos($s, 'other') === 0) return 'Lainnya';
            return $s;
        };

        foreach ($bookingCounts as $rawSource => $count) {
            $norm = $normalize($rawSource);
            $grouped[$norm] = ($grouped[$norm] ?? 0) + $count;
            if (stripos($rawSource, 'Lainnya:') === 0) {
                $customText = trim(substr($rawSource, 8));
                if ($customText) {
                    $details[$customText] = ($details[$customText] ?? 0) + $count;
                }
            }
        }

        foreach ($customerCounts as $rawSource => $count) {
            $norm = $normalize($rawSource);
            if (!isset($grouped[$norm])) {
                $grouped[$norm] = $count;
            } else {
                $grouped[$norm] = max($grouped[$norm], $count);
            }
            if (stripos($rawSource, 'Lainnya:') === 0) {
                $customText = trim(substr($rawSource, 8));
                if ($customText) {
                    $details[$customText] = ($details[$customText] ?? 0) + $count;
                }
            }
        }

        // Base channels list
        $defaultCategories = [
            'Instagram' => 0,
            'WhatsApp' => 0,
            'IG Capters' => 0,
            'Kabar / Teman' => 0,
            'TikTok' => 0,
            'Google' => 0,
            'Lainnya' => 0,
        ];
        foreach ($defaultCategories as $cat => $val) {
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = 0;
            }
        }

        // Filter out system technical sources if present (like generic 'Website' if other real sources exist)
        if (isset($grouped['Website']) && $grouped['Website'] > 0 && (array_sum($grouped) - $grouped['Website']) > 0) {
            // Keep it but labeled as Website Direct
        }

        // Sort descending (most popular / highest on top)
        arsort($grouped);

        $total = array_sum($grouped);
        $rankings = [];
        $rank = 1;

        $colors = [
            'Instagram' => ['bg' => 'from-pink-500 to-rose-500', 'text' => 'text-pink-600', 'badge' => 'bg-pink-50 text-pink-700 border-pink-200', 'bar' => 'bg-gradient-to-r from-pink-500 to-rose-500'],
            'WhatsApp' => ['bg' => 'from-emerald-500 to-green-600', 'text' => 'text-emerald-600', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'bar' => 'bg-gradient-to-r from-emerald-500 to-green-600'],
            'IG Capters' => ['bg' => 'from-purple-500 to-indigo-600', 'text' => 'text-purple-600', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200', 'bar' => 'bg-gradient-to-r from-purple-500 to-indigo-600'],
            'Kabar / Teman' => ['bg' => 'from-amber-400 to-amber-600', 'text' => 'text-amber-600', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200', 'bar' => 'bg-gradient-to-r from-amber-400 to-amber-600'],
            'TikTok' => ['bg' => 'from-stone-900 to-stone-700', 'text' => 'text-stone-900', 'badge' => 'bg-stone-100 text-stone-800 border-stone-300', 'bar' => 'bg-stone-900'],
            'Google' => ['bg' => 'from-blue-500 to-cyan-600', 'text' => 'text-blue-600', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200', 'bar' => 'bg-gradient-to-r from-blue-500 to-cyan-600'],
            'Lainnya' => ['bg' => 'from-stone-400 to-stone-500', 'text' => 'text-stone-600', 'badge' => 'bg-stone-50 text-stone-700 border-stone-200', 'bar' => 'bg-stone-400'],
        ];

        $topItem = null;
        foreach ($grouped as $channel => $count) {
            $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $styling = $colors[$channel] ?? ['bg' => 'from-stone-500 to-stone-600', 'text' => 'text-stone-700', 'badge' => 'bg-stone-100 text-stone-700 border-stone-200', 'bar' => 'bg-stone-500'];
            
            $item = [
                'rank' => $rank,
                'channel' => $channel,
                'count' => $count,
                'percentage' => $pct,
                'styling' => $styling,
                'is_top' => $rank === 1 && $count > 0,
            ];

            if ($rank === 1 && $count > 0) {
                $topItem = $item;
            }
            $rankings[] = $item;
            $rank++;
        }

        arsort($details);

        return [
            'rankings' => $rankings,
            'total' => $total,
            'top_source' => $topItem,
            'custom_details' => $details,
        ];
    }
}
