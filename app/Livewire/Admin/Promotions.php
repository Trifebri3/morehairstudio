<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Promotion\Models\Promotion;
use Illuminate\Support\Str;

class Promotions extends Component
{
    use WithPagination;

    public $search = '';
    public $isEditing = false;
    public $promotionId = null;

    // Form fields
    public $promo_code = '';
    public $discount_type = 'percentage';
    public $discount_value = 0;
    public $minimum_transaction = 0;
    public $maximum_discount = null;
    public $usage_limit = null;

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();
        session()->flash('message', "Data promo {$promotion->promo_code} berhasil dihapus.");
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $promotion = Promotion::findOrFail($id);
        $this->promotionId = $promotion->id;
        $this->promo_code = $promotion->promo_code;
        $this->discount_type = $promotion->discount_type;
        $this->discount_value = (int) $promotion->discount_value;
        $this->minimum_transaction = (int) $promotion->minimum_transaction;
        $this->maximum_discount = $promotion->maximum_discount ? (int) $promotion->maximum_discount : null;
        $this->usage_limit = $promotion->usage_limit;
        $this->isEditing = true;
    }

    public function save()
    {
        $rules = [
            'promo_code' => 'required|string|min:3|max:50|unique:promotions,promo_code,' . ($this->promotionId ?? 'NULL'),
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'minimum_transaction' => 'required|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
        ];

        $this->validate($rules);

        if ($this->promotionId) {
            $promotion = Promotion::findOrFail($this->promotionId);
            $promotion->update([
                'promo_code' => strtoupper($this->promo_code),
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'minimum_transaction' => $this->minimum_transaction,
                'maximum_discount' => $this->maximum_discount ?: null,
                'usage_limit' => $this->usage_limit ?: null,
            ]);
            session()->flash('message', "Promo {$promotion->promo_code} berhasil diperbarui.");
        } else {
            $promotion = Promotion::create([
                'promo_code' => strtoupper($this->promo_code),
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'minimum_transaction' => $this->minimum_transaction,
                'maximum_discount' => $this->maximum_discount ?: null,
                'usage_limit' => $this->usage_limit ?: null,
                'usage_count' => 0,
            ]);
            session()->flash('message', "Promo {$promotion->promo_code} berhasil dibuat.");
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
        $this->promotionId = null;
        $this->promo_code = '';
        $this->discount_type = 'percentage';
        $this->discount_value = 0;
        $this->minimum_transaction = 0;
        $this->maximum_discount = null;
        $this->usage_limit = null;
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $promotions = Promotion::where('promo_code', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.admin.promotions', compact('promotions'))->layout('layouts.admin');
    }
}
