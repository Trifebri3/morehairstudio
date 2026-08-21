<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Booking\Models\Booking;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard.
     */
    public function index()
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

        $users = User::with('outlet')->get();

        return view('admin.dashboard', compact(
            'totalBookings', 'totalRevenue', 'totalCustomers', 'totalOutlets', 'recentBookings', 'users'
        ));
    }
}
