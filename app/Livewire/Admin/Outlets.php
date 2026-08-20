<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Outlet\Models\Outlet;
use Illuminate\Support\Str;

class Outlets extends Component
{
    use WithPagination;

    public $search = '';
    public $isEditing = false;
    public $outletId = null;

    // Form fields
    public $name = '';
    public $slug = '';
    public $address = '';
    public $phone = '';
    public $whatsapp = '';
    public $status = 'active';

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $outlet = Outlet::findOrFail($id);
        $outlet->status = $outlet->status === 'active' ? 'inactive' : 'active';
        $outlet->save();
        session()->flash('message', "Status outlet {$outlet->name} berhasil diperbarui.");
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $outlet = Outlet::findOrFail($id);
        $this->outletId = $outlet->id;
        $this->name = $outlet->name;
        $this->slug = $outlet->slug;
        $this->address = $outlet->address;
        $this->phone = $outlet->phone;
        $this->whatsapp = $outlet->whatsapp;
        $this->status = $outlet->status;
        $this->isEditing = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:outlets,slug,' . ($this->outletId ?? 'NULL'),
            'address' => 'required|string|min:5|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ];

        $this->validate($rules);

        if ($this->outletId) {
            $outlet = Outlet::findOrFail($this->outletId);
            $outlet->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'address' => $this->address,
                'phone' => $this->phone,
                'whatsapp' => $this->whatsapp,
                'status' => $this->status,
            ]);
            session()->flash('message', "Outlet {$outlet->name} berhasil diperbarui.");
        } else {
            $outlet = Outlet::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'address' => $this->address,
                'phone' => $this->phone,
                'whatsapp' => $this->whatsapp,
                'status' => $this->status,
            ]);
            session()->flash('message', "Outlet {$outlet->name} berhasil dibuat.");
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
        $this->outletId = null;
        $this->name = '';
        $this->slug = '';
        $this->address = '';
        $this->phone = '';
        $this->whatsapp = '';
        $this->status = 'active';
    }

    public function updatedName($value)
    {
        if (!$this->outletId) {
            $this->slug = Str::slug($value);
        }
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $outlets = Outlet::where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.admin.outlets', compact('outlets'))->layout('layouts.admin');
    }
}
