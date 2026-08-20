<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Outlet\Models\Outlet;
use Illuminate\Support\Str;

class Stylists extends Component
{
    use WithPagination;

    public $search = '';
    public $isEditing = false;
    public $stylistId = null;

    // Form fields
    public $name = '';
    public $slug = '';
    public $outlet_id = '';
    public $specialization = '';
    public $status = 'active';
    public $bio = '';
    public $phone = '';

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $stylist = Stylist::findOrFail($id);
        $stylist->status = $stylist->status === 'active' ? 'inactive' : 'active';
        $stylist->save();
        session()->flash('message', "Status stylist {$stylist->name} berhasil diperbarui.");
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $stylist = Stylist::findOrFail($id);
        $this->stylistId = $stylist->id;
        $this->name = $stylist->name;
        $this->slug = $stylist->slug;
        $this->outlet_id = $stylist->outlet_id;
        $this->specialization = $stylist->specialization;
        $this->status = $stylist->status;
        $this->bio = $stylist->bio;
        $this->phone = $stylist->phone;
        $this->isEditing = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:stylists,slug,' . ($this->stylistId ?? 'NULL'),
            'outlet_id' => 'required|exists:outlets,id',
            'specialization' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:25',
        ];

        $this->validate($rules);

        if ($this->stylistId) {
            $stylist = Stylist::findOrFail($this->stylistId);
            $stylist->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'outlet_id' => $this->outlet_id,
                'specialization' => $this->specialization,
                'status' => $this->status,
                'bio' => $this->bio,
                'phone' => $this->phone,
            ]);
            session()->flash('message', "Stylist {$stylist->name} berhasil diperbarui.");
        } else {
            $stylist = Stylist::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'outlet_id' => $this->outlet_id,
                'specialization' => $this->specialization,
                'status' => $this->status,
                'bio' => $this->bio,
                'phone' => $this->phone,
            ]);
            session()->flash('message', "Stylist {$stylist->name} berhasil dibuat.");
        }

        $this->cancel();
    }

    public function cancel()
    {
        $this->resetForm();
        $this->isEditing = false;
    }

    private function resetForm()
    {
        $this->stylistId = null;
        $this->name = '';
        $this->slug = '';
        $this->outlet_id = '';
        $this->specialization = '';
        $this->status = 'active';
        $this->bio = '';
        $this->phone = '';
    }

    public function updatedName($value)
    {
        if (!$this->stylistId) {
            $this->slug = Str::slug($value);
        }
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $stylists = Stylist::where('name', 'like', '%' . $this->search . '%')
            ->with('outlet')
            ->paginate(10);

        $outlets = Outlet::all();

        return view('livewire.admin.stylists', compact('stylists', 'outlets'))->layout('layouts.admin');
    }
}
