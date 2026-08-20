<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\WhatsApp\Models\WhatsAppMessage;

class WhatsAppLogs extends Component
{
    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $logs = WhatsAppMessage::with('booking.customer')->latest()->get();

        return view('livewire.admin.whatsapp-logs', compact('logs'))
            ->layout('layouts.admin');
    }
}
