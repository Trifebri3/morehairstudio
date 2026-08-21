<?php

namespace App\Http\Controllers\Hairstylis;

use App\Http\Controllers\Controller;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Booking\Models\Booking;
use App\Domains\Attendance\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the stylist dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->first();

        if (!$stylist) {
            return view('hairstylis.dashboard', [
                'error_unlinked' => true
            ]);
        }

        // Get filter date
        $targetDate = $request->get('date', Carbon::today()->toDateString());

        // Build centered 7 days calendar stripe
        $centerDate = Carbon::parse($targetDate);
        $weekDays = [];
        for ($i = -3; $i <= 3; $i++) {
            $day = $centerDate->copy()->addDays($i);
            $weekDays[] = [
                'date' => $day->toDateString(),
                'dayName' => $day->isoFormat('ddd'),
                'dayNum' => $day->format('d'),
                'isToday' => $day->isToday(),
                'isActive' => $day->toDateString() === $targetDate,
            ];
        }

        // 1. Haircut schedules for the target date
        $schedules = Booking::where('stylist_id', $stylist->id)
            ->whereDate('booking_date', $targetDate)
            ->with(['customer', 'items.service'])
            ->get()
            ->sortBy(function($b) {
                return $b->items->first()?->start_time ?? '00:00:00';
            });

        // 2. Attendance status today
        $todayAttendance = Attendance::where('stylist_id', $stylist->id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();

        // 3. Last 30 days attendance logs
        $attendanceHistory = Attendance::where('stylist_id', $stylist->id)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // 4. Summaries & stats (Completed bookings MTD)
        $completedBookings = Booking::where('stylist_id', $stylist->id)
            ->where('status', 'completed')
            ->get();

        $mtdBookingsCount = $completedBookings->filter(function($b) {
            return Carbon::parse($b->booking_date)->isCurrentMonth();
        })->count();

        // 5. Build daily completed bookings for visual chart (last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $count = $completedBookings->filter(function($b) use ($day) {
                return Carbon::parse($b->booking_date)->toDateString() === $day->toDateString();
            })->count();
            
            $chartData[] = [
                'label' => $day->isoFormat('dd'),
                'count' => $count
            ];
        }

        return view('hairstylis.dashboard', [
            'stylist' => $stylist,
            'schedules' => $schedules,
            'searchDate' => $targetDate,
            'weekDays' => $weekDays,
            'todayAttendance' => $todayAttendance,
            'attendanceHistory' => $attendanceHistory,
            'mtdBookingsCount' => $mtdBookingsCount,
            'totalCompleted' => $completedBookings->count(),
            'chartData' => $chartData,
            'error_unlinked' => false
        ]);
    }

    /**
     * Update stylist profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->first();

        if (!$stylist) {
            return back()->with('error', 'Data Stylist tidak ditemukan.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'regex:/^(62|0)[0-9]{8,15}$/'],
            'specialization' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ], [
            'phone.regex' => 'Nomor WhatsApp wajib menggunakan format yang valid.',
            'email.email' => 'Format email wajib valid dan menggunakan email asli.',
        ]);

        $cleanedPhone = $request->phone;
        if (str_starts_with($cleanedPhone, '0')) {
            $cleanedPhone = '62' . substr($cleanedPhone, 1);
        }

        // Save to User
        $user->name = $request->name;
        $user->email = $request->email;
        if (\Schema::hasColumn('users', 'phone')) {
            $user->phone = $cleanedPhone;
        }
        $user->save();

        // Save to Stylist
        $stylist->name = $request->name;
        $stylist->phone = $cleanedPhone;
        $stylist->specialization = $request->specialization;
        $stylist->bio = $request->bio;
        $stylist->save();

        return back()->with('message', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Request leave.
     */
    public function requestLeave()
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->first();

        if (!$stylist) {
            return back()->with('error', 'Data Stylist tidak ditemukan.');
        }

        $stylist->status = 'pending_inactive';
        $stylist->save();

        return back()->with('message', 'Permintaan cuti telah diajukan. Menunggu persetujuan Admin Outlet.');
    }

    /**
     * Request account activation.
     */
    public function requestActivate()
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->first();

        if (!$stylist) {
            return back()->with('error', 'Data Stylist tidak ditemukan.');
        }

        $stylist->status = 'pending_active';
        $stylist->save();

        return back()->with('message', 'Permintaan aktivasi akun telah diajukan. Menunggu persetujuan Admin Outlet.');
    }

    /**
     * Confirm booking.
     */
    public function confirmBooking($id)
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->firstOrFail();

        $booking = Booking::where('stylist_id', $stylist->id)->findOrFail($id);
        $booking->status = 'confirmed';
        $booking->save();

        return back()->with('message', 'Booking berhasil dikonfirmasi.');
    }

    /**
     * Complete booking.
     */
    public function completeBooking($id)
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->firstOrFail();

        $booking = Booking::where('stylist_id', $stylist->id)->findOrFail($id);
        $booking->status = 'completed';
        $booking->save();

        if (class_exists(\App\Domains\Booking\Events\BookingCompleted::class)) {
            event(new \App\Domains\Booking\Events\BookingCompleted($booking));
        }

        return back()->with('message', 'Booking telah diselesaikan.');
    }
}
