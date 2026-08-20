<?php

namespace App\Livewire\Public;

use Livewire\Component;

class Privacy extends Component
{
    public function render()
    {
        return view('public.privacy')->layout('layouts.public');
    }
}
