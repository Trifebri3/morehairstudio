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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            // Auto-heal: Ensure a stylist record exists for any stylist user
            $slug = Str::slug($user->name);
            if (Stylist::where('slug', $slug)->exists()) {
                $slug .= '-' . $user->id;
            }
            $stylist = Stylist::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'slug' => $slug,
                'outlet_id' => $user->outlet_id ?? (\App\Domains\Outlet\Models\Outlet::first()->id ?? 1),
                'specialization' => 'Haircut & Styling',
                'status' => 'active',
                'rating' => 5.00,
                'phone' => '62812345678' . str_pad($user->id, 2, '0', STR_PAD_LEFT),
                'bio' => 'Professional Hair Stylist at MORE Hair Studio.'
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

        // Auto-complete active treatments whose service duration has elapsed
        Booking::autoCompleteDueBookings(null, $stylist->id);

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
            'instagram' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'phone.regex' => 'Nomor WhatsApp wajib menggunakan format yang valid.',
            'email.email' => 'Format email wajib valid dan menggunakan email asli.',
            'photo.image' => 'File foto profil wajib berformat gambar (JPG, PNG, WEBP).',
            'photo.max' => 'Ukuran foto profil maksimal 3MB.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        $cleanedPhone = $request->phone;
        if (str_starts_with($cleanedPhone, '0')) {
            $cleanedPhone = '62' . substr($cleanedPhone, 1);
        }

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('stylists', 'public');
            $stylist->photo_path = $photoPath;
            $stylist->photo = '/storage/' . $photoPath;
        }

        // Save to User
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if (\Schema::hasColumn('users', 'phone')) {
            $user->phone = $cleanedPhone;
        }
        $user->save();

        // Save to Stylist
        $stylist->name = $request->name;
        $stylist->phone = $cleanedPhone;
        $stylist->specialization = $request->specialization;
        $stylist->bio = $request->bio;
        $stylist->instagram = $request->instagram ? ltrim($request->instagram, '@') : null;
        $stylist->tiktok = $request->tiktok ? ltrim($request->tiktok, '@') : null;
        $stylist->save();

        return back()->with('message', 'Profil dan foto Anda berhasil diperbarui.');
    }

    /**
     * Request leave.
     */
    public function requestLeave(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->first();

        if (!$stylist) {
            return back()->with('error', 'Data Stylist tidak ditemukan.');
        }

        $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255']
        ]);

        \App\Domains\Stylist\Models\StylistLeave::create([
            'stylist_id' => $stylist->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'approved' // Automatically approve for this specific requirement, or 'pending' if it still requires admin approval. User says "otomatis tidak bisa booking", so we should make it approved or make the availability check include pending. Let's make it approved so it takes effect instantly.
        ]);

        $stylist->status = 'pending_inactive';
        $stylist->save();

        return back()->with('message', 'Cuti berhasil diajukan dari ' . \Carbon\Carbon::parse($request->start_date)->format('d M') . ' sampai ' . \Carbon\Carbon::parse($request->end_date)->format('d M') . '.');
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
     * Booking status is automated; direct manual confirmation is disabled.
     */
    public function confirmBooking($id)
    {
        return back()->with('message', 'Status booking dikelola otomatis oleh sistem saat customer check-in.');
    }

    /**
     * Booking completion is automated based on service duration.
     */
    public function completeBooking($id)
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->firstOrFail();

        $booking = Booking::where('stylist_id', $stylist->id)->findOrFail($id);
        if ($booking->shouldAutoComplete()) {
            $booking->autoCompleteIfDue();
            return back()->with('message', 'Booking telah diselesaikan secara otomatis berdasarkan durasi layanan.');
        }

        return back()->with('message', 'Layanan akan otomatis selesai saat durasi pengerjaan telah tercapai.');
    }

    /**
     * Request completion for an expired booking (Lupa Check In).
     */
    public function requestCompleteExpiredBooking($id)
    {
        $user = auth()->user();
        $stylist = Stylist::where('user_id', $user->id)->firstOrFail();

        $booking = Booking::where('stylist_id', $stylist->id)->findOrFail($id);
        
        if ($booking->status === 'expired') {
            $booking->status = 'pending_completion_approval';
            $booking->notes = 'Lupa Cek In - Menunggu ACC Admin' . ($booking->notes ? "\n" . $booking->notes : '');
            $booking->save();
            
            return back()->with('message', 'Pengajuan Selesai (Lupa Check In) telah dikirim ke antrean Admin.');
        }

        return back()->with('error', 'Status booking tidak valid.');
    }
}
