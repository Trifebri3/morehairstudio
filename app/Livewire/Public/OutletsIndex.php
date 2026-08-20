<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Domains\Outlet\Models\Outlet;

class OutletsIndex extends Component
{
    public function render()
    {
        $outlets = Outlet::where('status', 'active')->get();
        return view('public.outlets', compact('outlets'))->layout('layouts.public');
    }
}
