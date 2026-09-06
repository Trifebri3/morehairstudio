<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Payment\Models\Payment;
use App\Domains\Review\Models\Review;

class Booking extends Model
{
    protected $fillable = [
        'booking_code', 'booking_token', 'customer_id', 'outlet_id',
        'stylist_id', 'booking_date', 'checked_in_at', 'service_start_at',
        'service_end_at', 'service_duration_minutes', 'status', 'source',
        'total_amount', 'discount_amount', 'net_amount', 'promo_code', 'notes'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'checked_in_at' => 'datetime',
        'service_start_at' => 'datetime',
        'service_end_at' => 'datetime',
        'service_duration_minutes' => 'integer',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function ticket()
    {
        return $this->hasOne(BookingTicket::class);
    }

    /**
     * Calculate total service duration in minutes from booked items or services.
     */
    public function calculateServiceDuration(): int
    {
        if (!$this->relationLoaded('items')) {
            $this->load('items.service');
        }

        $totalMinutes = (int) $this->items->sum('duration');

        if ($totalMinutes <= 0) {
            foreach ($this->items as $item) {
                if ($item->service && $item->service->duration_minutes > 0) {
                    $totalMinutes += (int) $item->service->duration_minutes;
                }
            }
        }

        // Fallback default to 45 minutes if not specified
        return $totalMinutes > 0 ? $totalMinutes : 45;
    }

    /**
     * Start the service timing upon customer check-in.
     */
    public function startServiceTiming(?\Carbon\Carbon $startTime = null): void
    {
        $start = $startTime ?: \Carbon\Carbon::now();
        $duration = $this->calculateServiceDuration();
        $end = $start->copy()->addMinutes($duration);

        $this->checked_in_at = $start;
        $this->service_start_at = $start;
        $this->service_duration_minutes = $duration;
        $this->service_end_at = $end;
    }

    /**
     * Determine if this booking should automatically transition to 'completed'.
     */
    public function shouldAutoComplete(): bool
    {
        if (!in_array($this->status, ['checked_in', 'in_progress'])) {
            return false;
        }

        $now = \Carbon\Carbon::now();

        // If explicit service_end_at is set
        if ($this->service_end_at) {
            return $now->greaterThanOrEqualTo($this->service_end_at);
        }

        // Fallback: If checked_in_at is set, compute using duration
        if ($this->checked_in_at) {
            $duration = $this->service_duration_minutes ?: $this->calculateServiceDuration();
            $expectedEnd = \Carbon\Carbon::parse($this->checked_in_at)->addMinutes($duration);
            return $now->greaterThanOrEqualTo($expectedEnd);
        }

        return false;
    }

    /**
     * Auto complete this booking if due.
     */
    public function autoCompleteIfDue(): bool
    {
        if ($this->shouldAutoComplete()) {
            $action = app(\App\Domains\Booking\Actions\CompleteBooking::class);
            $action->execute($this, null);

            // Record status history specifying auto-completion
            BookingStatusHistory::create([
                'booking_id' => $this->id,
                'status' => 'completed',
                'changed_by' => null,
                'reason' => "Auto-Selesai: Durasi pengerjaan ({$this->service_duration_minutes} menit) telah terlewati."
            ]);

            return true;
        }

        return false;
    }

    /**
     * Batch auto complete all due bookings in the system or for specific outlet/stylist.
     */
    public static function autoCompleteDueBookings(?int $outletId = null, ?int $stylistId = null): int
    {
        $query = static::whereIn('status', ['checked_in', 'in_progress'])
            ->with(['items.service', 'payments']);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        if ($stylistId) {
            $query->where('stylist_id', $stylistId);
        }

        $activeBookings = $query->get();
        $completedCount = 0;

        foreach ($activeBookings as $booking) {
            if ($booking->autoCompleteIfDue()) {
                $completedCount++;
            }
        }

        return $completedCount;
    }

    /**
     * Remaining service duration in minutes (0 if completed or overdue).
     */
    public function getRemainingServiceMinutesAttribute(): int
    {
        if (!$this->service_end_at || in_array($this->status, ['completed', 'cancelled'])) {
            return 0;
        }

        $diff = \Carbon\Carbon::now()->diffInMinutes($this->service_end_at, false);
        return max(0, (int) $diff);
    }

    /**
     * Percentage progress of the current treatment (0% to 100%).
     */
    public function getServiceProgressPercentageAttribute(): int
    {
        if ($this->status === 'completed') {
            return 100;
        }

        if (!in_array($this->status, ['checked_in', 'in_progress']) || !$this->service_start_at || !$this->service_end_at) {
            return 0;
        }

        $totalSeconds = $this->service_start_at->diffInSeconds($this->service_end_at);
        if ($totalSeconds <= 0) return 100;

        $elapsedSeconds = $this->service_start_at->diffInSeconds(\Carbon\Carbon::now(), false);
        if ($elapsedSeconds <= 0) return 0;

        $percentage = ($elapsedSeconds / $totalSeconds) * 100;
        return (int) min(100, max(0, round($percentage)));
    }

    /**
     * Check if service is currently actively being worked on.
     */
    public function getIsServiceActiveAttribute(): bool
    {
        return in_array($this->status, ['checked_in', 'in_progress']);
    }

    /**
     * Determine if this booking should be auto-expired due to no-show past the check-in grace period.
     */
    public function shouldAutoExpire(): bool
    {
        if (!in_array($this->status, ['pending', 'confirmed'])) {
            return false;
        }

        $outlet = $this->outlet ?? Outlet::find($this->outlet_id);
        $graceActive = $outlet ? (bool)($outlet->checkin_grace_period_active ?? true) : true;
        if (!$graceActive) {
            return false;
        }

        $graceMinutes = $outlet ? (int)($outlet->checkin_grace_period_minutes ?? 15) : 15;

        // If booking date is in the past, it has expired
        $today = \Carbon\Carbon::today();
        if ($this->booking_date->lt($today)) {
            return true;
        }

        // If booking date is today, check if current time passed session start + grace period
        if ($this->booking_date->equalTo($today)) {
            $firstItem = $this->items->first();
            if ($firstItem && $firstItem->start_time) {
                $sessionStart = \Carbon\Carbon::parse($this->booking_date->toDateString() . ' ' . $firstItem->start_time);
                $expiryThreshold = $sessionStart->copy()->addMinutes($graceMinutes);
                return \Carbon\Carbon::now()->greaterThan($expiryThreshold);
            }
        }

        return false;
    }

    /**
     * Auto-expire this booking and record status history.
     */
    public function autoExpireIfDue(): bool
    {
        if ($this->shouldAutoExpire()) {
            $outlet = $this->outlet ?? Outlet::find($this->outlet_id);
            $graceMinutes = $outlet ? (int)($outlet->checkin_grace_period_minutes ?? 15) : 15;
            $firstItem = $this->items->first();
            $slotTime = $firstItem ? substr($firstItem->start_time, 0, 5) : '-';

            $this->update(['status' => 'expired']);

            BookingStatusHistory::create([
                'booking_id' => $this->id,
                'status' => 'expired',
                'changed_by' => null,
                'reason' => "Booking hangus otomatis: Pelanggan belum check-in setelah melewati batas toleransi {$graceMinutes} menit (jadwal {$slotTime} WIB). Slot kembali kosong untuk pelanggan lain."
            ]);

            event(new \App\Domains\Booking\Events\BookingExpired($this));

            return true;
        }

        return false;
    }

    /**
     * Batch auto-expire all no-show bookings in the system or for a specific outlet.
     */
    public static function autoExpireNoShows(?int $outletId = null): int
    {
        $query = static::whereIn('status', ['pending', 'confirmed'])
            ->whereDate('booking_date', '<=', \Carbon\Carbon::today())
            ->with(['items', 'outlet']);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $candidates = $query->get();
        $expiredCount = 0;

        foreach ($candidates as $booking) {
            if ($booking->autoExpireIfDue()) {
                $expiredCount++;
            }
        }

        return $expiredCount;
    }
}
