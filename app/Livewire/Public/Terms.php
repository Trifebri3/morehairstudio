<?php

namespace App\Livewire\Public;

use Livewire\Component;

class Terms extends Component
{
    public function render()
    {
        return view('public.terms')->layout('layouts.public');
    }
}
