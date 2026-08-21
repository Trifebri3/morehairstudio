<?php

namespace App\Http\Controllers\Outlet;

use App\Http\Controllers\Controller;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;
use App\Domains\Booking\Actions\CompleteBooking;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Attendance\Models\Attendance as StylistAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OutletDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'outlet_admin' && $user->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $outletId = $user->outlet_id;
        $outlet = Outlet::findOrFail($outletId);

        $attendanceStart = $outlet->attendance_start_time ? substr($outlet->attendance_start_time, 0, 5) : '07:00';
        $attendanceEnd = $outlet->attendance_end_time ? substr($outlet->attendance_end_time, 0, 5) : '09:00';
        $clockOutStart = $outlet->clock_out_start_time ? substr($outlet->clock_out_start_time, 0, 5) : '16:00';
        $clockOutEnd = $outlet->clock_out_end_time ? substr($outlet->clock_out_end_time, 0, 5) : '18:00';
        $bookingLeadTime = $outlet->booking_lead_time_hours ?? 1;
        $checkinGraceActive = (bool)($outlet->checkin_grace_period_active ?? true);
        $checkinGraceMinutes = $outlet->checkin_grace_period_minutes ?? 15;

        $description = $outlet->description ?? '';
        $mapIframe = $outlet->map_iframe ?? '';
        $gallery = is_array($outlet->gallery) ? $outlet->gallery : [];

        // Load all services to populate active toggles and custom overrides
        $services = \App\Domains\Service\Models\Service::with('category')->get();
        $currentServices = DB::table('outlet_services')
            ->where('outlet_id', $outlet->id)
            ->get()
            ->keyBy('service_id');

        $selectedServices = [];
        $customPrices = [];
        $customDurations = [];

        foreach ($services as $s) {
            $pivot = $currentServices->get($s->id);
            $selectedServices[$s->id] = $pivot ? (bool)$pivot->is_active : false;
            $customPrices[$s->id] = $pivot ? $pivot->price : '';
            $customDurations[$s->id] = $pivot ? $pivot->duration : '';
        }

        $totalBookings = Booking::where('outlet_id', $outletId)->count();
        $totalRevenue = Booking::where('outlet_id', $outletId)->where('status', 'completed')->sum('net_amount');

        $recentBookings = Booking::where('outlet_id', $outletId)
            ->with(['customer', 'stylist'])
            ->latest()
            ->take(5)
            ->get();

        return view('outlet.dashboard', compact(
            'outlet', 'attendanceStart', 'attendanceEnd', 'clockOutStart', 'clockOutEnd',
            'bookingLeadTime', 'checkinGraceActive', 'checkinGraceMinutes', 'description',
            'mapIframe', 'gallery', 'selectedServices', 'customPrices', 'customDurations',
            'totalBookings', 'totalRevenue', 'recentBookings', 'services'
        ));
    }

    public function saveSettings(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->outlet_id) {
            return back()->with('error', 'Outlet tidak ditemukan.');
        }

        $request->validate([
            'attendanceStart' => ['required', 'string', 'regex:/^[0-2][0-9]:[0-5][0-9]$/'],
            'attendanceEnd' => ['required', 'string', 'regex:/^[0-2][0-9]:[0-5][0-9]$/'],
            'clockOutStart' => ['required', 'string', 'regex:/^[0-2][0-9]:[0-5][0-9]$/'],
            'clockOutEnd' => ['required', 'string', 'regex:/^[0-2][0-9]:[0-5][0-9]$/'],
            'bookingLeadTime' => ['required', 'integer', 'min:0', 'max:72'],
            'checkinGraceActive' => ['required', 'boolean'],
            'checkinGraceMinutes' => ['required', 'integer', 'min:1', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'mapIframe' => ['nullable', 'string'],
            'newPhotos.*' => ['nullable', 'image', 'max:5120'], // Max 5MB
        ]);

        $outlet = Outlet::find($user->outlet_id);
        if ($outlet) {
            $outlet->attendance_start_time = $request->attendanceStart;
            $outlet->attendance_end_time = $request->attendanceEnd;
            $outlet->clock_out_start_time = $request->clockOutStart;
            $outlet->clock_out_end_time = $request->clockOutEnd;
            $outlet->booking_lead_time_hours = $request->bookingLeadTime;
            $outlet->checkin_grace_period_active = $request->checkinGraceActive;
            $outlet->checkin_grace_period_minutes = $request->checkinGraceMinutes;

            // Handle Photo Gallery update
            $gallery = is_array($outlet->gallery) ? $outlet->gallery : [];
            if ($request->has('removed_gallery_indices')) {
                $removed = json_decode($request->removed_gallery_indices, true);
                if (is_array($removed)) {
                    foreach ($removed as $idx) {
                        unset($gallery[$idx]);
                    }
                    $gallery = array_values($gallery);
                }
            }

            // Handle new uploads
            if ($request->hasFile('newPhotos')) {
                foreach ($request->file('newPhotos') as $photo) {
                    $path = $photo->store('outlets/gallery', 'public');
                    $gallery[] = '/storage/' . $path;
                }
            }

            $outlet->gallery = $gallery;
            $outlet->description = $request->description;
            $outlet->map_iframe = $request->mapIframe;
            $outlet->save();

            // Sync services pivots
            $services = \App\Domains\Service\Models\Service::all();
            foreach ($services as $s) {
                $isActive = $request->has('selectedServices.' . $s->id);
                $price = $request->input('customPrices.' . $s->id);
                $duration = $request->input('customDurations.' . $s->id);

                $exists = DB::table('outlet_services')
                    ->where('outlet_id', $outlet->id)
                    ->where('service_id', $s->id)
                    ->exists();

                if ($exists) {
                    DB::table('outlet_services')
                        ->where('outlet_id', $outlet->id)
                        ->where('service_id', $s->id)
                        ->update([
                            'is_active' => (bool)$isActive,
                            'price' => !empty($price) ? $price : null,
                            'duration' => !empty($duration) ? $duration : null,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('outlet_services')->insert([
                        'outlet_id' => $outlet->id,
                        'service_id' => $s->id,
                        'is_active' => (bool)$isActive,
                        'price' => !empty($price) ? $price : null,
                        'duration' => !empty($duration) ? $duration : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            return back()->with('message', 'Pengaturan outlet berhasil disimpan.');
        }

        return back()->with('error', 'Outlet gagal disimpan.');
    }

    public function bookings(Request $request)
    {
        $search = $request->get('search', '');
        $statusFilter = $request->get('statusFilter', '');

        $outletId = auth()->user()->outlet_id ?? 1;

        $bookings = Booking::where('outlet_id', $outletId)
            ->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function ($c) use ($search) {
                      $c->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                  });
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->with(['customer', 'stylist', 'items.service'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('outlet.bookings', compact('bookings', 'search', 'statusFilter'));
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $newStatus = $request->input('status');

        if ($newStatus === 'completed') {
            $complete = new CompleteBooking();
            $complete->execute($booking);
        } else {
            $booking->update(['status' => $newStatus]);
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'status' => $newStatus,
                'reason' => 'Status updated via Outlet Admin Dashboard.'
            ]);
        }

        return back()->with('message', "Status booking {$booking->booking_code} berhasil diubah ke {$newStatus}.");
    }

    public function stylists(Request $request)
    {
        $search = $request->get('search', '');
        $outletId = auth()->user()->outlet_id ?? 1;

        $stylists = Stylist::where('outlet_id', $outletId)
            ->where('name', 'like', '%' . $search . '%')
            ->get();

        return view('outlet.stylists', compact('stylists', 'search'));
    }

    public function toggleStylistStatus($id)
    {
        $outletId = auth()->user()->outlet_id ?? 1;
        $stylist = Stylist::where('outlet_id', $outletId)->findOrFail($id);
        
        $stylist->status = $stylist->status === 'active' ? 'inactive' : 'active';
        $stylist->save();

        return back()->with('message', "Status stylist {$stylist->name} berhasil diperbarui.");
    }

    public function approveStylistStatus($id)
    {
        $outletId = auth()->user()->outlet_id ?? 1;
        $stylist = Stylist::where('outlet_id', $outletId)->findOrFail($id);
        
        if ($stylist->status === 'pending_active') {
            $stylist->status = 'active';
            $msg = "Permintaan aktivasi akun {$stylist->name} disetujui.";
        } elseif ($stylist->status === 'pending_inactive' || $stylist->status === 'pending_leave') {
            $stylist->status = 'inactive';
            $msg = "Permintaan cuti {$stylist->name} disetujui.";
        } else {
            $msg = "Status stylist diperbarui.";
        }
        
        $stylist->save();
        return back()->with('message', $msg);
    }

    public function rejectStylistStatus($id)
    {
        $outletId = auth()->user()->outlet_id ?? 1;
        $stylist = Stylist::where('outlet_id', $outletId)->findOrFail($id);
        
        if ($stylist->status === 'pending_active') {
            $stylist->status = 'inactive';
            $msg = "Permintaan aktivasi akun {$stylist->name} ditolak.";
        } elseif ($stylist->status === 'pending_inactive' || $stylist->status === 'pending_leave') {
            $stylist->status = 'active';
            $msg = "Permintaan cuti {$stylist->name} ditolak.";
        } else {
            $msg = "Permintaan stylist ditolak.";
        }
        
        $stylist->save();
        return back()->with('message', $msg);
    }

    public function attendance(Request $request)
    {
        $dateFilter = $request->get('dateFilter', Carbon::today()->toDateString());
        $search = $request->get('search', '');
        $outletId = auth()->user()->outlet_id ?? 1;

        $attendances = StylistAttendance::whereHas('stylist', function ($q) use ($outletId, $search) {
                $q->where('outlet_id', $outletId)
                  ->where('name', 'like', '%' . $search . '%');
            })
            ->when($dateFilter, function ($q) use ($dateFilter) {
                $q->where('date', $dateFilter);
            })
            ->with('stylist')
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        return view('outlet.attendance', compact('attendances', 'dateFilter', 'search'));
    }
}
