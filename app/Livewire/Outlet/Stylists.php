<?php

namespace App\Livewire\Outlet;

use Livewire\Component;
use App\Domains\Stylist\Models\Stylist;

class Stylists extends Component
{
    public $search = '';

    public function toggleStatus($id)
    {
        $outletId = auth()->user()->outlet_id ?? 1;
        $stylist = Stylist::where('outlet_id', $outletId)->findOrFail($id);
        
        $stylist->status = $stylist->status === 'active' ? 'inactive' : 'active';
        $stylist->save();

        session()->flash('message', "Status stylist {$stylist->name} berhasil diperbarui.");
    }

    public function approveStatus($id)
    {
        $outletId = auth()->user()->outlet_id ?? 1;
        $stylist = Stylist::where('outlet_id', $outletId)->findOrFail($id);
        
        if ($stylist->status === 'pending_active') {
            $stylist->status = 'active';
            session()->flash('message', "Permintaan aktivasi akun {$stylist->name} disetujui.");
        } elseif ($stylist->status === 'pending_inactive' || $stylist->status === 'pending_leave') {
            $stylist->status = 'inactive';
            session()->flash('message', "Permintaan cuti {$stylist->name} disetujui.");
        }
        $stylist->save();
    }

    public function rejectStatus($id)
    {
        $outletId = auth()->user()->outlet_id ?? 1;
        $stylist = Stylist::where('outlet_id', $outletId)->findOrFail($id);
        
        if ($stylist->status === 'pending_active') {
            $stylist->status = 'inactive';
            session()->flash('message', "Permintaan aktivasi akun {$stylist->name} ditolak.");
        } elseif ($stylist->status === 'pending_inactive' || $stylist->status === 'pending_leave') {
            $stylist->status = 'active';
            session()->flash('message', "Permintaan cuti {$stylist->name} ditolak.");
        }
        $stylist->save();
    }

    public function render()
    {
        $outletId = auth()->user()->outlet_id ?? 1;

        $stylists = Stylist::where('outlet_id', $outletId)
            ->where('name', 'like', '%' . $this->search . '%')
            ->get();

        return view('livewire.outlet.stylists', compact('stylists'))->layout('layouts.admin');
    }
}
