<?php

namespace App\Livewire\Tablet;

use Livewire\Component;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Attendance\Models\Attendance as StylistAttendance;
use Carbon\Carbon;

class Attendance extends Component
{
    public $successMessage = null;

    public function clockIn($stylistId)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = StylistAttendance::where('stylist_id', $stylistId)->where('date', $today)->first();

        if ($attendance) {
            session()->flash('error', 'Stylist ini sudah Clock-In hari ini.');
            return;
        }

        // Attendance rules: Late if check-in is past 10:15
        $status = 'present';
        if ($now->format('H:i:s') > '10:15:00') {
            $status = 'late';
        }

        StylistAttendance::create([
            'stylist_id' => $stylistId,
            'date' => $today,
            'clock_in' => $now,
            'status' => $status,
            'device_info' => 'TABLET-OUTLET-01'
        ]);

        $stylist = Stylist::find($stylistId);
        $this->successMessage = "Clock-In berhasil! Selamat bekerja, {$stylist->name} ({$now->format('H:i')}).";
    }

    public function clockOut($stylistId)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = StylistAttendance::where('stylist_id', $stylistId)->where('date', $today)->first();

        if (!$attendance) {
            session()->flash('error', 'Stylist belum Clock-In hari ini.');
            return;
        }

        if ($attendance->clock_out) {
            session()->flash('error', 'Stylist ini sudah Clock-Out hari ini.');
            return;
        }

        $attendance->update([
            'clock_out' => $now
        ]);

        $stylist = Stylist::find($stylistId);
        $this->successMessage = "Clock-Out berhasil! Terima kasih atas dedikasi Anda hari ini, {$stylist->name} ({$now->format('H:i')}).";
    }

    public function render()
    {
        $tabletOutletId = session('tablet_outlet_id', 1);
        // Load stylists of local outlet
        $stylists = Stylist::where('outlet_id', $tabletOutletId)
            ->where('status', 'active')
            ->get();

        $today = Carbon::today()->toDateString();
        $attendances = StylistAttendance::where('date', $today)
            ->whereIn('stylist_id', $stylists->pluck('id'))
            ->get()
            ->keyBy('stylist_id');

        return view('livewire.tablet.attendance', compact('stylists', 'attendances'))->layout('layouts.tablet');
    }
}
