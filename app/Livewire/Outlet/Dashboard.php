<?php

namespace App\Livewire\Outlet;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Domains\Booking\Models\Booking;
use App\Domains\Outlet\Models\Outlet;

class Dashboard extends Component
{
    use WithFileUploads;

    public $attendanceStart = '07:00';
    public $attendanceEnd = '09:00';
    public $clockOutStart = '16:00';
    public $clockOutEnd = '18:00';
    public $bookingLeadTime = 1;
    public $checkinGraceActive = true;
    public $checkinGraceMinutes = 15;

    // Profile Settings
    public $description = '';
    public $mapIframe = '';
    public $gallery = [];
    public $newPhotos = [];

    // Services modifications
    public $selectedServices = [];
    public $customPrices = [];
    public $customDurations = [];

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->outlet_id) {
            $outlet = Outlet::find($user->outlet_id);
            if ($outlet) {
                $this->attendanceStart = $outlet->attendance_start_time ? substr($outlet->attendance_start_time, 0, 5) : '07:00';
                $this->attendanceEnd = $outlet->attendance_end_time ? substr($outlet->attendance_end_time, 0, 5) : '09:00';
                $this->clockOutStart = $outlet->clock_out_start_time ? substr($outlet->clock_out_start_time, 0, 5) : '16:00';
                $this->clockOutEnd = $outlet->clock_out_end_time ? substr($outlet->clock_out_end_time, 0, 5) : '18:00';
                $this->bookingLeadTime = $outlet->booking_lead_time_hours ?? 1;
                $this->checkinGraceActive = (bool)($outlet->checkin_grace_period_active ?? true);
                $this->checkinGraceMinutes = $outlet->checkin_grace_period_minutes ?? 15;

                $this->description = $outlet->description ?? '';
                $this->mapIframe = $outlet->map_iframe ?? '';
                $this->gallery = is_array($outlet->gallery) ? $outlet->gallery : [];

                // Load all services to populate active toggles and custom overrides
                $services = \App\Domains\Service\Models\Service::all();
                $currentServices = \Illuminate\Support\Facades\DB::table('outlet_services')
                    ->where('outlet_id', $outlet->id)
                    ->get()
                    ->keyBy('service_id');

                foreach ($services as $s) {
                    $pivot = $currentServices->get($s->id);
                    $this->selectedServices[$s->id] = $pivot ? (bool)$pivot->is_active : false;
                    $this->customPrices[$s->id] = $pivot ? $pivot->price : '';
                    $this->customDurations[$s->id] = $pivot ? $pivot->duration : '';
                }
            }
        }
    }

    public function removePhoto($index)
    {
        if (isset($this->gallery[$index])) {
            unset($this->gallery[$index]);
            $this->gallery = array_values($this->gallery);
        }
    }

    public function saveSettings()
    {
        $user = auth()->user();
        if (!$user || !$user->outlet_id) {
            session()->flash('error', 'Outlet tidak ditemukan.');
            return;
        }

        $this->validate([
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
            $outlet->attendance_start_time = $this->attendanceStart;
            $outlet->attendance_end_time = $this->attendanceEnd;
            $outlet->clock_out_start_time = $this->clockOutStart;
            $outlet->clock_out_end_time = $this->clockOutEnd;
            $outlet->booking_lead_time_hours = $this->bookingLeadTime;
            $outlet->checkin_grace_period_active = $this->checkinGraceActive;
            $outlet->checkin_grace_period_minutes = $this->checkinGraceMinutes;

            // Handle file uploads
            foreach ($this->newPhotos as $photo) {
                $path = $photo->store('outlets/gallery', 'public');
                $this->gallery[] = '/storage/' . $path;
            }
            $this->newPhotos = []; // Clear file input queue

            $outlet->gallery = $this->gallery;
            $outlet->description = $this->description;
            $outlet->map_iframe = $this->mapIframe;
            $outlet->save();

            // Sync services pivots
            foreach ($this->selectedServices as $serviceId => $isActive) {
                $price = !empty($this->customPrices[$serviceId]) ? $this->customPrices[$serviceId] : null;
                $duration = !empty($this->customDurations[$serviceId]) ? $this->customDurations[$serviceId] : null;

                $exists = \Illuminate\Support\Facades\DB::table('outlet_services')
                    ->where('outlet_id', $outlet->id)
                    ->where('service_id', $serviceId)
                    ->exists();

                if ($exists) {
                    \Illuminate\Support\Facades\DB::table('outlet_services')
                        ->where('outlet_id', $outlet->id)
                        ->where('service_id', $serviceId)
                        ->update([
                            'is_active' => (bool)$isActive,
                            'price' => $price,
                            'duration' => $duration,
                            'updated_at' => now()
                        ]);
                } else {
                    \Illuminate\Support\Facades\DB::table('outlet_services')->insert([
                        'outlet_id' => $outlet->id,
                        'service_id' => $serviceId,
                        'is_active' => (bool)$isActive,
                        'price' => $price,
                        'duration' => $duration,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            session()->flash('message', 'Pengaturan outlet berhasil disimpan.');
        }
    }

    public function render()
    {
        $user = auth()->user();
        if ($user->role !== 'outlet_admin' && $user->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $outletId = $user->outlet_id;

        $totalBookings = Booking::where('outlet_id', $outletId)->count();
        $totalRevenue = Booking::where('outlet_id', $outletId)->where('status', 'completed')->sum('net_amount');

        $recentBookings = Booking::where('outlet_id', $outletId)
            ->with(['customer', 'stylist'])
            ->latest()
            ->take(5)
            ->get();

        $services = \App\Domains\Service\Models\Service::with('category')->get();

        return view('livewire.outlet.dashboard', compact(
            'totalBookings', 'totalRevenue', 'recentBookings', 'services'
        ))->layout('layouts.admin');
    }
}
