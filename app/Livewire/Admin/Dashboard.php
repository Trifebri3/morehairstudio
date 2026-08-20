<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\Booking\Models\Booking;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;

class Dashboard extends Component
{
    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $totalBookings = Booking::count();
        $totalRevenue = Booking::where('status', 'completed')->sum('net_amount');
        $totalCustomers = Customer::count();
        $totalOutlets = Outlet::count();

        $recentBookings = Booking::with(['customer', 'outlet', 'stylist'])
            ->latest()
            ->take(5)
            ->get();

        $users = \App\Models\User::with('outlet')->get();

        return view('livewire.admin.dashboard', compact(
            'totalBookings', 'totalRevenue', 'totalCustomers', 'totalOutlets', 'recentBookings', 'users'
        ))->layout('layouts.admin');
    }
}
