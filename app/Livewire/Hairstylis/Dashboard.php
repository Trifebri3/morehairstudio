<?php

namespace App\Livewire\Hairstylis;

use Livewire\Component;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Booking\Models\Booking;
use App\Domains\Attendance\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public ?Stylist $stylist = null;
    
    // Date search filter
    public string $searchDate = '';

    // Geolocation coordinates
    public $gpsLat = null;
    public $gpsLng = null;
    public $deviceInfo = '';

    // Profile inputs
    public string $editName = '';
    public string $editEmail = '';
    public string $editPhone = '';
    public string $editSpecialization = '';
    public string $editBio = '';

    protected $listeners = ['setGpsLocation' => 'applyGpsLocation'];

    public function mount()
    {
        $this->searchDate = Carbon::today()->toDateString();
        
        $user = auth()->user();
        $this->stylist = Stylist::where('user_id', $user->id)->first();

        if ($this->stylist) {
            $this->editName = $this->stylist->name;
            $this->editEmail = $user->email;
            $this->editPhone = $this->stylist->phone ?? $user->phone ?? '';
            $this->editSpecialization = $this->stylist->specialization ?? '';
            $this->editBio = $this->stylist->bio ?? '';
        }
    }

    public function applyGpsLocation($lat, $lng, $device = '')
    {
        $this->gpsLat = $lat;
        $this->gpsLng = $lng;
        $this->deviceInfo = $device;
    }

    public function requestLeave()
    {
        if (!$this->stylist) {
            session()->flash('error', 'Data Stylist tidak ditemukan.');
            return;
        }

        $this->stylist->status = 'pending_inactive'; // or pending_leave
        $this->stylist->save();

        session()->flash('message', 'Permintaan cuti telah diajukan. Menunggu persetujuan Admin Outlet.');
    }

    public function requestActivate()
    {
        if (!$this->stylist) {
            session()->flash('error', 'Data Stylist tidak ditemukan.');
            return;
        }

        $this->stylist->status = 'pending_active';
        $this->stylist->save();

        session()->flash('message', 'Permintaan aktivasi akun telah diajukan. Menunggu persetujuan Admin Outlet.');
    }

    public function updateProfile()
    {
        if (!$this->stylist) {
            session()->flash('error', 'Data Stylist tidak ditemukan.');
            return;
        }

        $user = auth()->user();

        // Strict validation: real email (standard Laravel validation) and WA number (international format starts with 62 or 0)
        $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'editPhone' => ['required', 'string', 'regex:/^(62|0)[0-9]{8,15}$/'],
            'editSpecialization' => ['required', 'string', 'max:255'],
            'editBio' => ['nullable', 'string', 'max:1000'],
        ], [
            'editPhone.regex' => 'Nomor WhatsApp wajib menggunakan format yang valid.',
            'editEmail.email' => 'Format email wajib valid dan menggunakan email asli.',
        ]);

        // Clean phone number format
        $cleanedPhone = $this->editPhone;
        if (str_starts_with($cleanedPhone, '0')) {
            $cleanedPhone = '62' . substr($cleanedPhone, 1);
        }

        // Save to User
        $user->name = $this->editName;
        $user->email = $this->editEmail;
        if (\Schema::hasColumn('users', 'phone')) {
            $user->phone = $cleanedPhone;
        }
        $user->save();

        // Save to Stylist
        $this->stylist->name = $this->editName;
        $this->stylist->phone = $cleanedPhone;
        $this->stylist->specialization = $this->editSpecialization;
        $this->stylist->bio = $this->editBio;
        $this->stylist->save();

        session()->flash('message', 'Profil Anda berhasil diperbarui.');
    }

    public function clockIn()
    {
        if (!$this->stylist) {
            session()->flash('error', 'Data Stylist tidak ditemukan.');
            return;
        }

        $today = Carbon::today()->toDateString();
        
        $exists = Attendance::where('stylist_id', $this->stylist->id)->where('date', $today)->exists();
        if ($exists) {
            session()->flash('error', 'Anda sudah melakukan Clock In hari ini.');
            return;
        }

        $now = Carbon::now();
        // Shift starts at 09:00:00. If clock in is after 09:00:00, status is 'late'
        $status = $now->format('H:i:s') > '09:00:00' ? 'late' : 'present';

        Attendance::create([
            'stylist_id' => $this->stylist->id,
            'date' => $today,
            'clock_in' => $now,
            'status' => $status,
            'location_lat' => $this->gpsLat,
            'location_lng' => $this->gpsLng,
            'device_info' => $this->deviceInfo ?: request()->header('User-Agent'),
        ]);

        session()->flash('message', 'Clock In berhasil direkam! Status: ' . ($status === 'late' ? 'Terlambat' : 'Tepat Waktu'));
    }

    public function clockOut()
    {
        if (!$this->stylist) {
            session()->flash('error', 'Data Stylist tidak ditemukan.');
            return;
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('stylist_id', $this->stylist->id)->where('date', $today)->first();

        if (!$attendance) {
            session()->flash('error', 'Anda belum melakukan Clock In hari ini.');
            return;
        }

        if ($attendance->clock_out) {
            session()->flash('error', 'Anda sudah melakukan Clock Out hari ini.');
            return;
        }

        $attendance->clock_out = Carbon::now();
        $attendance->save();

        session()->flash('message', 'Clock Out berhasil direkam! Sampai jumpa besok.');
    }

    public function confirmBooking($id)
    {
        $booking = Booking::where('stylist_id', $this->stylist->id)->findOrFail($id);
        $booking->status = 'confirmed';
        $booking->save();

        session()->flash('message', 'Booking berhasil dikonfirmasi.');
    }

    public function completeBooking($id)
    {
        $booking = Booking::where('stylist_id', $this->stylist->id)->findOrFail($id);
        $booking->status = 'completed';
        $booking->save();

        // Fire status history event if necessary or notify
        if (class_exists(\App\Domains\Booking\Events\BookingCompleted::class)) {
            event(new \App\Domains\Booking\Events\BookingCompleted($booking));
        }

        session()->flash('message', 'Booking telah diselesaikan.');
    }

    public function render()
    {
        if (!$this->stylist) {
            return view('livewire.hairstylis.dashboard', [
                'error_unlinked' => true
            ])->layout('layouts.admin');
        }

        // Search dates
        $targetDate = $this->searchDate ?: Carbon::today()->toDateString();

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
        $schedules = Booking::where('stylist_id', $this->stylist->id)
            ->whereDate('booking_date', $targetDate)
            ->with(['customer', 'items.service'])
            ->get()
            ->sortBy(function($b) {
                return $b->items->first()?->start_time ?? '00:00:00';
            });

        // 2. Attendance status today
        $todayAttendance = Attendance::where('stylist_id', $this->stylist->id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();

        // 3. Last 30 days attendance logs
        $attendanceHistory = Attendance::where('stylist_id', $this->stylist->id)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // 4. Summaries & stats (Completed bookings MTD)
        $completedBookings = Booking::where('stylist_id', $this->stylist->id)
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

        return view('livewire.hairstylis.dashboard', [
            'schedules' => $schedules,
            'weekDays' => $weekDays,
            'todayAttendance' => $todayAttendance,
            'attendanceHistory' => $attendanceHistory,
            'mtdBookingsCount' => $mtdBookingsCount,
            'totalCompleted' => $completedBookings->count(),
            'chartData' => $chartData,
            'error_unlinked' => false
        ])->layout('layouts.admin');
    }
}
