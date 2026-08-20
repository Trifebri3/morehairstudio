<?php

namespace App\Livewire\Outlet;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Attendance\Models\Attendance as StylistAttendance;
use App\Domains\Stylist\Models\Stylist;
use Carbon\Carbon;

class Attendance extends Component
{
    use WithPagination;

    public $dateFilter = '';
    public $search = '';

    protected $updatesQueryString = ['dateFilter', 'search'];

    public function mount()
    {
        $this->dateFilter = Carbon::today()->toDateString();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $outletId = auth()->user()->outlet_id ?? 1;

        $attendances = StylistAttendance::whereHas('stylist', function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId)
                  ->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->dateFilter, function ($q) {
                $q->where('date', $this->dateFilter);
            })
            ->with('stylist')
            ->latest('date')
            ->paginate(10);

        return view('livewire.outlet.attendance', compact('attendances'))->layout('layouts.admin');
    }
}
