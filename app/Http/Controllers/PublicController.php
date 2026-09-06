<?php

namespace App\Http\Controllers;

use App\Domains\Review\Models\Review;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Service\Models\Service;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home(Request $request)
    {
        $reviews = Review::where('status', 'approved')
            ->with(['customer', 'stylist', 'outlet'])
            ->latest()
            ->take(3)
            ->get();

        $outlets = Outlet::where('status', 'active')->get();
        $stylists = Stylist::where('status', 'active')->get();
        $services = Service::where('is_active', true)->with('category')->get();
        $categories = \App\Domains\Service\Models\ServiceCategory::whereHas('services', function ($q) {
            $q->where('is_active', true);
        })->with(['services' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        return view('public.home', compact('reviews', 'outlets', 'stylists', 'services', 'categories'));
    }

    public function about(Request $request)
    {
        return view('public.about');
    }

    public function services(Request $request)
    {
        $services = Service::where('is_active', true)->with('category')->get();
        return view('public.services', compact('services'));
    }

    public function stylists(Request $request)
    {
        $stylists = Stylist::where('status', 'active')->with('outlet')->get();
        return view('public.stylists', compact('stylists'));
    }

    public function outlets(Request $request)
    {
        $outlets = Outlet::where('status', 'active')->get();
        return view('public.outlets', compact('outlets'));
    }

    public function outletShow(Request $request, $slug)
    {
        $outlet = Outlet::where('slug', $slug)->where('status', 'active')->firstOrFail();

        // Get stylists active in this outlet
        $stylists = Stylist::where('outlet_id', $outlet->id)->where('status', 'active')->get();

        // Get services enabled in this outlet (pivot overrides checked)
        $outletServices = $outlet->services()
            ->wherePivot('is_active', true)
            ->with('category')
            ->get();

        // Group services by category for clean UI display
        $servicesByCategory = [];
        foreach ($outletServices as $s) {
            $catName = $s->category ? $s->category->name : 'General';
            
            $servicesByCategory[$catName][] = [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'price' => $s->pivot->price ?? $s->default_price,
                'duration' => $s->pivot->duration ?? $s->default_duration,
            ];
        }

        // Keep it aligned with resources/views/livewire/public/outlet-show.blade.php layout (let's check where the view is rendered)
        // Note: Livewire component rendered public/outlet-show, we can move or duplicate this view cleanly to public/outlet-show.blade.php!
        return view('public.outlet-show', compact('outlet', 'stylists', 'servicesByCategory'));
    }

    public function terms(Request $request)
    {
        return view('public.terms');
    }

    public function privacy(Request $request)
    {
        return view('public.privacy');
    }

    public function schedule(Request $request)
    {
        $outlets = Outlet::where('status', 'active')->get();
        $defaultOutlet = Outlet::where('status', 'active')->whereHas('stylists', function($q) {
            $q->where('status', 'active');
        })->first() ?? $outlets->first();

        $defaultOutletId = $defaultOutlet ? $defaultOutlet->id : 2;
        $selectedOutletId = (int)$request->get('outlet_id', $defaultOutletId);

        $stylists = Stylist::where('outlet_id', $selectedOutletId)->where('status', 'active')->get();
        $services = Service::where('is_active', true)->get();

        $selectedDate = $request->get('date', \Carbon\Carbon::today()->toDateString());

        return view('public.schedule', compact('outlets', 'selectedOutletId', 'stylists', 'services', 'selectedDate'));
    }

    public function stylistProfile(Request $request, $slug)
    {
        $cleanSlug = strtolower(trim($slug));
        
        $stylist = Stylist::where('status', 'active')
            ->where(function($q) use ($cleanSlug) {
                $q->where('slug', $cleanSlug)
                  ->orWhereRaw('LOWER(name) = ?', [$cleanSlug])
                  ->orWhereRaw("LOWER(REPLACE(name, ' ', '-')) = ?", [$cleanSlug])
                  ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$cleanSlug]);
            })
            ->with(['outlet', 'schedules' => function($q) {
                $q->orderBy('day_of_week');
            }, 'reviews' => function($q) {
                $q->latest()->take(10);
            }])
            ->first();

        if (!$stylist) {
            abort(404);
        }

        // Get services available in this stylist's outlet
        $outlet = $stylist->outlet;
        $services = [];
        $servicesByCategory = [];

        if ($outlet) {
            $outletServices = $outlet->services()
                ->wherePivot('is_active', true)
                ->with('category')
                ->get();

            foreach ($outletServices as $s) {
                $catName = $s->category ? $s->category->name : 'Signature Services';
                $serviceItem = [
                    'id' => $s->id,
                    'name' => $s->name,
                    'description' => $s->description,
                    'formatted_description_html' => $s->formatted_description_html,
                    'price' => $s->pivot->price ?? $s->default_price,
                    'duration' => $s->pivot->duration ?? $s->default_duration,
                ];
                $services[] = $serviceItem;
                $servicesByCategory[$catName][] = $serviceItem;
            }
        }

        // Fallback to active services if outlet has no specific attachments
        if (empty($services)) {
            $allServices = Service::where('is_active', true)->with('category')->get();
            foreach ($allServices as $s) {
                $catName = $s->category ? $s->category->name : 'Signature Services';
                $serviceItem = [
                    'id' => $s->id,
                    'name' => $s->name,
                    'description' => $s->description,
                    'formatted_description_html' => $s->formatted_description_html,
                    'price' => $s->default_price,
                    'duration' => $s->default_duration,
                ];
                $services[] = $serviceItem;
                $servicesByCategory[$catName][] = $serviceItem;
            }
        }

        $completedCount = $stylist->bookings()->where('status', 'completed')->count();

        $daysMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return view('public.stylist-profile', compact(
            'stylist', 
            'outlet', 
            'services', 
            'servicesByCategory', 
            'completedCount',
            'daysMap'
        ));
    }
}
