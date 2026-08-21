<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Outlet\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $search = $request->get('search', '');
        $outlets = Outlet::where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%');
            })
            ->paginate(10)
            ->withQueryString();

        $editingOutlet = null;
        if ($request->has('edit')) {
            $editingOutlet = Outlet::find($request->edit);
        }

        $isCreating = $request->has('create');

        return view('admin.outlets', compact('outlets', 'search', 'editingOutlet', 'isCreating'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:outlets,slug',
            'address' => 'required|string|min:5|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        $outlet = Outlet::create($request->only(['name', 'slug', 'address', 'phone', 'whatsapp', 'status']));

        return redirect()->route('admin.outlets')->with('message', "Outlet {$outlet->name} berhasil dibuat.");
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $outlet = Outlet::findOrFail($id);

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:outlets,slug,' . $id,
            'address' => 'required|string|min:5|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        $outlet->update($request->only(['name', 'slug', 'address', 'phone', 'whatsapp', 'status']));

        return redirect()->route('admin.outlets')->with('message', "Outlet {$outlet->name} berhasil diperbarui.");
    }

    public function toggleStatus($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $outlet = Outlet::findOrFail($id);
        $outlet->status = $outlet->status === 'active' ? 'inactive' : 'active';
        $outlet->save();

        return back()->with('message', "Status outlet {$outlet->name} berhasil diperbarui.");
    }
}
