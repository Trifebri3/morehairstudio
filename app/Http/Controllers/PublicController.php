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

        $outlets = Outlet::where('status', 'active')->take(2)->get();
        $stylists = Stylist::where('status', 'active')->take(4)->get();
        $services = Service::where('is_active', true)->take(4)->get();

        return view('public.home', compact('reviews', 'outlets', 'stylists', 'services'));
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
}
