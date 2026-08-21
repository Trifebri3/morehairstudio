<?php

namespace App\Http\Controllers\Tablet;

use App\Http\Controllers\Controller;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;
use App\Domains\Booking\Actions\CheckInBooking;
use App\Domains\Booking\Actions\CompleteBooking;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Attendance\Models\Attendance;
use App\Domains\Payment\Models\Payment;
use App\Domains\Outlet\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TabletKioskController extends Controller
{
    public function dashboard(Request $request)
    {
        if ($request->has('tablet_outlet_id')) {
            session(['tablet_outlet_id' => (int)$request->query('tablet_outlet_id')]);
        }
        return view('tablet.dashboard');
    }

    public function checkIn(Request $request)
    {
        $tabletOutletId = session('tablet_outlet_id', 1);
        $searchQuery = $request->get('searchQuery', '');
        $booking = null;
        $errorMessage = null;
        $successMessage = null;

        if ($searchQuery) {
            $search = trim($searchQuery);

            // Intercept stylist QR check-in
            if (str_starts_with($search, 'stylist:') || preg_match('/^MH-ST-\d+$/', $search)) {
                $stylistId = null;
                if (str_starts_with($search, 'stylist:')) {
                    $stylistId = (int) substr($search, 8);
                } else {
                    $stylistId = (int) substr($search, 6);
                }

                $stylist = Stylist::find($stylistId);
                if (!$stylist) {
                    $errorMessage = 'Stylist tidak ditemukan.';
                } else if ($stylist->outlet_id !== $tabletOutletId) {
                    $errorMessage = "Stylist ini terdaftar di outlet: {$stylist->outlet->name}. Silakan absen di outlet tersebut.";
                } else {
                    $today = Carbon::today()->toDateString();
                    $attendance = Attendance::where('stylist_id', $stylist->id)->where('date', $today)->first();
                    
                    $now = Carbon::now();
                    $nowTime = $now->format('H:i:s');
                    $outlet = $stylist->outlet;
                    $startLimit = $outlet->attendance_start_time ?? '07:00:00';
                    $endLimit = $outlet->attendance_end_time ?? '09:00:00';

                    if (!$attendance) {
                        // Clock In
                        if ($nowTime < $startLimit || $nowTime > $endLimit) {
                            $errorMessage = "Absen ditolak. Jam absen masuk saat ini adalah {$startLimit} - {$endLimit} (Sekarang: " . $now->format('H:i') . ").";
                        } else {
                            $status = $nowTime > '09:00:00' ? 'late' : 'present';

                            Attendance::create([
                                'stylist_id' => $stylist->id,
                                'date' => $today,
                                'clock_in' => $now,
                                'status' => $status,
                                'device_info' => 'Tablet Check-In QR Scanner',
                            ]);

                            $successMessage = "Absen Masuk (Clock In) Berhasil! Selamat bekerja, {$stylist->name}.";
                        }
                    } else {
                        // Clock Out
                        if ($attendance->clock_out) {
                            $errorMessage = "Anda sudah melakukan Clock In & Clock Out hari ini.";
                        } else {
                            $clockOutStart = $outlet->clock_out_start_time ?? '16:00:00';
                            $clockOutEnd = $outlet->clock_out_end_time ?? '18:00:00';

                            if ($nowTime < $clockOutStart || $nowTime > $clockOutEnd) {
                                $formattedStart = substr($clockOutStart, 0, 5);
                                $formattedEnd = substr($clockOutEnd, 0, 5);
                                $errorMessage = "Absen pulang ditolak. Jam absen pulang saat ini adalah {$formattedStart} - {$formattedEnd} (Sekarang: " . $now->format('H:i') . ").";
                            } else {
                                $attendance->clock_out = $now;
                                $attendance->save();

                                $successMessage = "Absen Pulang (Clock Out) Berhasil! Sampai jumpa besok, {$stylist->name}.";
                            }
                        }
                    }
                }
            } else {
                // Lookup by booking code or token
                $booking = Booking::where('booking_code', $search)
                    ->orWhere('booking_token', $search)
                    ->with(['customer', 'outlet', 'stylist', 'items.service'])
                    ->first();

                if (!$booking) {
                    $errorMessage = 'Booking tidak ditemukan. Pastikan kode Anda benar.';
                } else if ($booking->outlet_id !== $tabletOutletId) {
                    $errorMessage = "Booking ini terdaftar untuk outlet: {$booking->outlet->name}. Silakan check-in di outlet tersebut.";
                    $booking = null;
                }
            }
        }

        return view('tablet.check-in', compact('booking', 'searchQuery', 'errorMessage', 'successMessage'));
    }

    public function processCheckIn(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $checkIn = new CheckInBooking();
            $tabletOutletId = session('tablet_outlet_id', 1);
            $checkIn->execute($booking, $tabletOutletId);

            return redirect()->route('tablet.check-in')
                ->with('success_overlay', [
                    'type' => 'checkin',
                    'message' => "Check-in berhasil! Selamat datang, {$booking->customer->name}."
                ]);
        } catch (\Exception $e) {
            return redirect()->route('tablet.check-in', ['searchQuery' => $booking->booking_code])
                ->with('error', $e->getMessage());
        }
    }

    public function attendance(Request $request)
    {
        $tabletOutletId = session('tablet_outlet_id', 1);
        $stylists = Stylist::where('outlet_id', $tabletOutletId)
            ->where('status', 'active')
            ->get();

        $today = Carbon::today()->toDateString();
        $attendances = Attendance::where('date', $today)
            ->whereIn('stylist_id', $stylists->pluck('id'))
            ->get()
            ->keyBy('stylist_id');

        return view('tablet.attendance', compact('stylists', 'attendances'));
    }

    public function clockIn(Request $request, $id)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::where('stylist_id', $id)->where('date', $today)->first();

        if ($attendance) {
            return back()->with('error', 'Stylist ini sudah Clock-In hari ini.');
        }

        // Attendance rules: Late if check-in is past 10:15
        $status = 'present';
        if ($now->format('H:i:s') > '10:15:00') {
            $status = 'late';
        }

        Attendance::create([
            'stylist_id' => $id,
            'date' => $today,
            'clock_in' => $now,
            'status' => $status,
            'device_info' => 'TABLET-OUTLET-01'
        ]);

        $stylist = Stylist::findOrFail($id);
        return back()->with('message', "Clock-In berhasil! Selamat bekerja, {$stylist->name} ({$now->format('H:i')}).");
    }

    public function clockOut(Request $request, $id)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::where('stylist_id', $id)->where('date', $today)->first();

        if (!$attendance) {
            return back()->with('error', 'Stylist belum Clock-In hari ini.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Stylist ini sudah Clock-Out hari ini.');
        }

        $attendance->update([
            'clock_out' => $now
        ]);

        $stylist = Stylist::findOrFail($id);
        return back()->with('message', "Clock-Out berhasil! Terima kasih atas dedikasi Anda hari ini, {$stylist->name} ({$now->format('H:i')}).");
    }

    public function queue(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $tabletOutletId = session('tablet_outlet_id', 1);

        $bookings = Booking::where('outlet_id', $tabletOutletId)
            ->where('booking_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'in_progress', 'completed'])
            ->with(['customer', 'stylist', 'items.service'])
            ->get();

        return view('tablet.queue', compact('bookings'));
    }

    public function startService(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'in_progress']);

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => 'in_progress',
            'reason' => 'Stylist started treatment.'
        ]);

        return back()->with('message', "Treatment dimulai untuk booking: {$booking->booking_code}.");
    }

    public function completeService(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $complete = new CompleteBooking();
        $complete->execute($booking);

        return back()->with('message', "Treatment selesai! Booking {$booking->booking_code} ditandai completed.");
    }

    public function styscreen(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['outlet_admin', 'super_admin'])) {
            return view('tablet.styscreen-login');
        }

        $outletId = auth()->user()->outlet_id ?? session('tablet_outlet_id', 1);
        $outlet = Outlet::find($outletId);
        $today = Carbon::today()->toDateString();

        $searchQuery = $request->get('searchQuery', '');

        // Load today's bookings for this outlet
        $query = Booking::where('outlet_id', $outletId)
            ->whereDate('booking_date', $today)
            ->with(['customer', 'stylist', 'items.service', 'payments']);

        if ($searchQuery) {
            $query->where(function ($sub) use ($searchQuery) {
                $sub->where('booking_code', 'like', "%{$searchQuery}%")
                    ->orWhereHas('customer', function ($c) use ($searchQuery) {
                        $c->where('name', 'like', "%{$searchQuery}%")
                          ->orWhere('phone', 'like', "%{$searchQuery}%");
                    });
            });
        }

        $bookings = $query->latest()->get();

        // Categorize bookings for Styscreen lanes
        $unpaidBookings = $bookings->filter(function ($b) {
            $isPaid = $b->payments->where('status', 'paid')->isNotEmpty();
            return !$isPaid && $b->status !== 'cancelled' && $b->status !== 'expired';
        });

        $paidActiveBookings = $bookings->filter(function ($b) {
            $isPaid = $b->payments->where('status', 'paid')->isNotEmpty();
            return $isPaid && in_array($b->status, ['confirmed', 'checked_in', 'in_progress']);
        });

        $completedBookings = $bookings->filter(function ($b) {
            return $b->status === 'completed';
        });

        $selectedBooking = null;
        if ($request->has('pay_booking_id')) {
            $selectedBooking = Booking::with(['customer', 'stylist', 'items.service', 'payments'])
                ->find($request->pay_booking_id);
        }

        return view('tablet.styscreen', compact(
            'unpaidBookings', 'paidActiveBookings', 'completedBookings', 'outlet',
            'selectedBooking', 'searchQuery'
        ));
    }

    public function styscreenLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            $user = Auth::user();
            if (in_array($user->role, ['outlet_admin', 'super_admin'])) {
                $outletId = $user->outlet_id ?? 1;
                session(['tablet_outlet_id' => $outletId]);
                return redirect()->route('tablet.styscreen');
            } else {
                Auth::logout();
                return back()->with('error', 'Akses ditolak. Hanya Admin Outlet atau Super Admin yang dapat mengakses layar ini.');
            }
        }

        return back()->with('error', 'Email atau password salah.');
    }

    public function styscreenLogout()
    {
        Auth::logout();
        session()->forget('tablet_outlet_id');
        return redirect()->route('tablet.styscreen');
    }

    public function styscreenPay(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string'
        ]);

        try {
            $booking = Booking::with('payments')->findOrFail($id);
            
            // Execute completion
            $completeAction = new CompleteBooking();
            $completeAction->execute($booking, auth()->id());

            $ref = $request->transaction_reference ?: 'EDC-' . strtoupper(Str::random(6));

            // Handle payment records
            if ($booking->payments->isEmpty()) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => $request->payment_method,
                    'transaction_reference' => $ref,
                    'amount' => $booking->net_amount,
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            } else {
                foreach ($booking->payments as $payment) {
                    $payment->update([
                        'status' => 'paid',
                        'payment_method' => $request->payment_method,
                        'transaction_reference' => $ref,
                        'paid_at' => now()
                    ]);
                }
            }

            return redirect()->route('tablet.styscreen')
                ->with('message', "Booking {$booking->booking_code} berhasil dilunasi via " . strtoupper($request->payment_method) . "!");
        } catch (\Exception $e) {
            return redirect()->route('tablet.styscreen', ['pay_booking_id' => $id])
                ->with('error', $e->getMessage());
        }
    }
}
