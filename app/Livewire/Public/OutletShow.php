<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Service\Models\Service;
use Illuminate\Support\Facades\DB;

class OutletShow extends Component
{
    public $slug;

    public function mount($slug)
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $outlet = Outlet::where('slug', $this->slug)->where('status', 'active')->firstOrFail();

        // Get stylists active in this outlet
        $stylists = Stylist::where('outlet_id', $outlet->id)->where('status', 'active')->get();

        // Get services enabled in this outlet (pivot overrides checked)
        $outletServices = $outlet->services()
            ->wherePivot('is_active', true)
            ->with('category')
            ->get();

        // Group services by category for clean UI display
        $servicesByCategory = [];
        foreach ($outletServices as $s) {
            $catName = $s->category ? $s->category->name : 'General';
            
            $servicesByCategory[$catName][] = [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'price' => $s->pivot->price ?? $s->default_price,
                'duration' => $s->pivot->duration ?? $s->default_duration,
            ];
        }

        return view('livewire.public.outlet-show', compact('outlet', 'stylists', 'servicesByCategory'))
            ->layout('layouts.public');
    }
}
