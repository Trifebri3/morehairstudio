<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Domains\Stylist\Models\Stylist;

class StylistsIndex extends Component
{
    public function render()
    {
        $stylists = Stylist::where('status', 'active')->with('outlet')->get();
        return view('public.stylists', compact('stylists'))->layout('layouts.public');
    }
}
