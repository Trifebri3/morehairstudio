<?php

namespace App\Domains\CRM\Services;

use App\Domains\Customer\Models\Customer;
use Carbon\Carbon;

class RFMService
{
    /**
     * Calculate RFM values and categorize a customer.
     */
    public static function analyze(Customer $customer): array
    {
        // 1. Recency & Frequency: check if relations are pre-loaded to eliminate N+1 queries
        $completedBookings = $customer->relationLoaded('bookings')
            ? $customer->bookings->filter(fn($b) => $b->status === 'completed')
            : $customer->bookings()->where('status', 'completed')->get();

        $completedTxs = $customer->relationLoaded('posTransactions')
            ? $customer->posTransactions->filter(fn($tx) => $tx->status === 'completed')
            : $customer->posTransactions()->where('status', 'completed')->get();

        $lastBooking = $completedBookings->sortByDesc('booking_date')->first();
        $lastTx = $completedTxs->sortByDesc('completed_at')->first();

        $lastVisitDate = null;
        if ($lastBooking && $lastTx) {
            $lastVisitDate = Carbon::parse($lastBooking->booking_date)->max(Carbon::parse($lastTx->completed_at));
        } elseif ($lastBooking) {
            $lastVisitDate = Carbon::parse($lastBooking->booking_date);
        } elseif ($lastTx) {
            $lastVisitDate = Carbon::parse($lastTx->completed_at);
        }

        $recencyDays = $lastVisitDate ? Carbon::now()->diffInDays($lastVisitDate) : 999;

        // Recency Score (1-5, lower days = higher score)
        if ($recencyDays <= 30) $rScore = 5;
        elseif ($recencyDays <= 60) $rScore = 4;
        elseif ($recencyDays <= 120) $rScore = 3;
        elseif ($recencyDays <= 180) $rScore = 2;
        else $rScore = 1;

        // 2. Frequency: total visits (completed bookings + POS transactions)
        $bookingCount = $completedBookings->count();
        $txCount = $completedTxs->count();
        $totalVisits = $bookingCount + $txCount;

        // Frequency Score (1-5, higher visits = higher score)
        if ($totalVisits >= 15) $fScore = 5;
        elseif ($totalVisits >= 8) $fScore = 4;
        elseif ($totalVisits >= 4) $fScore = 3;
        elseif ($totalVisits >= 2) $fScore = 2;
        else $fScore = 1;

        // 3. Monetary: total spending
        $bookingSpend = (float)$completedBookings->sum('net_amount');
        $txSpend = (float)$completedTxs->sum('grand_total');
        $totalSpending = $bookingSpend + $txSpend;

        // Monetary Score (1-5, higher spend = higher score)
        if ($totalSpending >= 2000000) $mScore = 5;
        elseif ($totalSpending >= 1000000) $mScore = 4;
        elseif ($totalSpending >= 500000) $mScore = 3;
        elseif ($totalSpending >= 150000) $mScore = 2;
        else $mScore = 1;

        // Segment mapping based on RFM Scores
        $segment = self::getSegment($rScore, $fScore, $mScore);

        return [
            'recency_days' => $recencyDays,
            'total_visits' => $totalVisits,
            'total_spending' => $totalSpending,
            'r_score' => $rScore,
            'f_score' => $fScore,
            'm_score' => $mScore,
            'segment' => $segment,
            'rfm_code' => "{$rScore}{$fScore}{$mScore}"
        ];
    }

    /**
     * Map RFM scores to a specific customer persona segment.
     */
    private static function getSegment(int $r, int $f, int $m): string
    {
        $avgScore = ($r + $f + $m) / 3;

        if ($r >= 4 && $f >= 4 && $m >= 4) {
            return 'Champions';
        }
        if ($r >= 3 && $f >= 4 && $m >= 3) {
            return 'Loyal Customers';
        }
        if ($r >= 4 && $f >= 2 && $m >= 2) {
            return 'Potential Loyalists';
        }
        if ($r >= 4 && $f <= 2 && $m <= 2) {
            return 'New Customers';
        }
        if ($r === 3 && $f <= 2 && $m <= 2) {
            return 'Promising';
        }
        if ($r <= 2 && $f >= 3 && $m >= 3) {
            return 'At Risk';
        }
        if ($r <= 2 && $r > 1 && ($f <= 2 || $m <= 2)) {
            return 'Need Attention';
        }
        return 'Lost Customers';
    }
}
