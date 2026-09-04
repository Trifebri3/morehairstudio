<?php

namespace App\Http\Controllers\Public;

use App\Domains\Booking\Models\Booking;
use App\Domains\Service\Models\Service;
use App\Domains\Service\Models\ServiceCategory;
use App\Domains\Stylist\Models\Stylist;
use App\Http\Controllers\Controller;

class StylistBioController extends Controller
{
    public function show(string $slug)
    {
        // 1. Find the stylist by slug or case-insensitive slug or ID
        $stylist = Stylist::where('slug', $slug)
            ->where('status', 'active')
            ->with(['outlet'])
            ->first();

        if (! $stylist) {
            $stylist = Stylist::whereRaw('LOWER(slug) = ?', [strtolower($slug)])
                ->where('status', 'active')
                ->with(['outlet'])
                ->first();
        }

        if (! $stylist && is_numeric($slug)) {
            $stylist = Stylist::where('id', $slug)
                ->where('status', 'active')
                ->with(['outlet'])
                ->first();
        }

        if (! $stylist) {
            abort(404, 'Profil Hair Artist tidak ditemukan.');
        }

        // 2. Fetch active services
        $services = Service::where('is_active', true)
            ->with('category')
            ->get();

        $categories = ServiceCategory::whereHas('services', function ($q) {
            $q->where('is_active', true);
        })->get();

        // 3. Fetch current bookings today for schedule preview
        $today = now()->toDateString();
        $bookedToday = Booking::where('stylist_id', $stylist->id)
            ->whereDate('booking_date', $today)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->with('items')
            ->get()
            ->flatMap(fn ($b) => $b->items)
            ->map(fn ($item) => [
                'start' => substr($item->start_time, 0, 5),
                'end' => substr($item->end_time, 0, 5),
            ])
            ->sortBy('start')
            ->values();

        return view('public.stylist-bio', compact('stylist', 'services', 'categories', 'bookedToday'));
    }
}
