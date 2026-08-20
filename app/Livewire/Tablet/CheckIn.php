<?php

namespace App\Livewire\Tablet;

use Livewire\Component;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Actions\CheckInBooking;

use App\Domains\Stylist\Models\Stylist;
use App\Domains\Attendance\Models\Attendance;
use Carbon\Carbon;

class CheckIn extends Component
{
    public $searchQuery = '';
    public $booking = null;
    public $errorMessage = null;
    public $successMessage = null;

    public function search()
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->booking = null;

        if (empty($this->searchQuery)) {
            $this->errorMessage = 'Silakan masukkan kode booking atau token QR.';
            return;
        }

        $search = trim($this->searchQuery);

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
                $this->errorMessage = 'Stylist tidak ditemukan.';
                return;
            }

            // Validate outlet boundary
            $tabletOutletId = session('tablet_outlet_id', 1);
            if ($stylist->outlet_id !== $tabletOutletId) {
                $this->errorMessage = "Stylist ini terdaftar di outlet: {$stylist->outlet->name}. Silakan absen di outlet tersebut.";
                return;
            }

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
                    $this->errorMessage = "Absen ditolak. Jam absen masuk saat ini adalah {$startLimit} - {$endLimit} (Sekarang: " . $now->format('H:i') . ").";
                    return;
                }

                $status = $nowTime > '09:00:00' ? 'late' : 'present';

                Attendance::create([
                    'stylist_id' => $stylist->id,
                    'date' => $today,
                    'clock_in' => $now,
                    'status' => $status,
                    'device_info' => 'Tablet Check-In QR Scanner',
                ]);

                $this->successMessage = "Absen Masuk (Clock In) Berhasil! Selamat bekerja, {$stylist->name}.";
                $this->dispatch('show-success-overlay', type: 'absen', message: $this->successMessage);
            } else {
                // Clock Out
                if ($attendance->clock_out) {
                    $this->errorMessage = "Anda sudah melakukan Clock In & Clock Out hari ini.";
                    return;
                }

                $clockOutStart = $outlet->clock_out_start_time ?? '16:00:00';
                $clockOutEnd = $outlet->clock_out_end_time ?? '18:00:00';

                if ($nowTime < $clockOutStart || $nowTime > $clockOutEnd) {
                    $formattedStart = substr($clockOutStart, 0, 5);
                    $formattedEnd = substr($clockOutEnd, 0, 5);
                    $this->errorMessage = "Absen pulang ditolak. Jam absen pulang saat ini adalah {$formattedStart} - {$formattedEnd} (Sekarang: " . $now->format('H:i') . ").";
                    return;
                }

                $attendance->clock_out = $now;
                $attendance->save();

                $this->successMessage = "Absen Pulang (Clock Out) Berhasil! Sampai jumpa besok, {$stylist->name}.";
                $this->dispatch('show-success-overlay', type: 'absen', message: $this->successMessage);
            }

            $this->searchQuery = '';
            return;
        }

        // Lookup by booking code or token
        $booking = Booking::where('booking_code', $search)
            ->orWhere('booking_token', $search)
            ->with(['customer', 'outlet', 'stylist', 'items.service'])
            ->first();

        if (!$booking) {
            $this->errorMessage = 'Booking tidak ditemukan. Pastikan kode Anda benar.';
            return;
        }

        // Validate outlet boundaries
        $tabletOutletId = session('tablet_outlet_id', 1);
        if ($booking->outlet_id !== $tabletOutletId) {
            $this->errorMessage = "Booking ini terdaftar untuk outlet: {$booking->outlet->name}. Silakan check-in di outlet tersebut.";
            return;
        }

        $this->booking = $booking;
    }

    public function processCheckIn()
    {
        if (!$this->booking) return;

        try {
            $checkIn = new CheckInBooking();
            $tabletOutletId = session('tablet_outlet_id', 1);
            $checkIn->execute($this->booking, $tabletOutletId);

            $this->successMessage = "Check-in berhasil! Selamat datang, {$this->booking->customer->name}.";
            $this->dispatch('show-success-overlay', type: 'checkin', message: $this->successMessage);
            $this->booking = null;
            $this->searchQuery = '';
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Simulation Helper: load test booking seed
     */
    public function simulateQrScan()
    {
        // Load first seeded booking
        $booking = Booking::where('booking_code', 'MOR-180826-A1B2C')->first();
        if ($booking) {
            $this->searchQuery = $booking->booking_code;
            $this->search();
        }
    }

    public function render()
    {
        return view('livewire.tablet.check-in')->layout('layouts.tablet');
    }
}
