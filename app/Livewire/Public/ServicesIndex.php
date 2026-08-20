<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Domains\Service\Models\Service;

class ServicesIndex extends Component
{
    public function render()
    {
        $services = Service::where('is_active', true)->with('category')->get();
        return view('public.services', compact('services'))->layout('layouts.public');
    }
}
