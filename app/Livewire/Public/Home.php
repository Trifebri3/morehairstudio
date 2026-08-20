<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Domains\Review\Models\Review;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Service\Models\Service;

class Home extends Component
{
    public function render()
    {
        $reviews = Review::where('status', 'approved')
            ->with(['customer', 'stylist', 'outlet'])
            ->latest()
            ->take(3)
            ->get();

        $outlets = Outlet::where('status', 'active')->take(2)->get();
        $stylists = Stylist::where('status', 'active')->take(4)->get();
        $services = Service::where('is_active', true)->take(4)->get();

        return view('public.home', compact('reviews', 'outlets', 'stylists', 'services'))
            ->layout('layouts.public');
    }
}
