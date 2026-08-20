<?php

namespace App\Livewire\Tablet;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('tablet.dashboard')->layout('layouts.tablet');
    }
}
