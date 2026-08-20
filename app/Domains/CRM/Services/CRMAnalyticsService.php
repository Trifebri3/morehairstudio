<?php

namespace App\Domains\CRM\Services;

use App\Domains\Customer\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CRMAnalyticsService
{
    /**
     * Compute comprehensive derived customer behavior analytics.
     */
    public static function getBehavior(Customer $customer): array
    {
        // 1. Visit logs
        $bookings = $customer->bookings()->where('status', 'completed')->orderBy('booking_date', 'asc')->get();
        $posTxs = $customer->posTransactions()->where('status', 'completed')->orderBy('completed_at', 'asc')->get();

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
        $bookingSpend = $customer->bookings()->where('status', 'completed')->sum('net_amount');
        $txSpend = $customer->posTransactions()->where('status', 'completed')->sum('grand_total');
        $totalSpending = $bookingSpend + $txSpend;

        $averageSpending = $totalVisits > 0 ? round($totalSpending / $totalVisits, 2) : 0;

        // Highest transaction
        $highestBooking = $customer->bookings()->where('status', 'completed')->max('net_amount') ?: 0;
        $highestTx = $customer->posTransactions()->where('status', 'completed')->max('grand_total') ?: 0;
        $highestSpending = max($highestBooking, $highestTx);

        // 3. Booking Stats
        $totalBookings = $customer->bookings()->count();
        $completedBookings = $customer->bookings()->where('status', 'completed')->count();
        $cancelledBookings = $customer->bookings()->where('status', 'cancelled')->count();
        $noShowBookings = $customer->bookings()->where('status', 'no_show')->count();

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
}
