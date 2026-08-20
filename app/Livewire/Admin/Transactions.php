<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\POS\Models\PosTransaction;
use App\Domains\POS\Services\POSTransactionService;
use Illuminate\Support\Facades\Gate;

class Transactions extends Component
{
    use WithPagination;

    public $search = '';
    public $filterOutlet = '';
    public $filterPaymentMethod = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Modal Receipt reprint
    public $selectedTxId = null;
    public $showReprintModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterOutlet' => ['except' => ''],
        'filterPaymentMethod' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewReceipt($id)
    {
        $this->selectedTxId = $id;
        $this->showReprintModal = true;
    }

    public function closeReprintModal()
    {
        $this->showReprintModal = false;
        $this->selectedTxId = null;
    }

    public function processRefund($id)
    {
        Gate::authorize('pos.refund');

        try {
            $transaction = PosTransaction::findOrFail($id);
            POSTransactionService::refund($transaction);
            
            session()->flash('message', "Transaksi {$transaction->transaction_number} berhasil di-refund. Stok produk dan poin loyalitas telah dikembalikan.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal memproses refund: " . $e->getMessage());
        }
    }

    public function render()
    {
        Gate::authorize('pos.view');

        // Apply Multi-outlet filter isolation
        if (auth()->user()->role === 'outlet_admin') {
            $this->filterOutlet = auth()->user()->outlet_id;
        }

        $query = PosTransaction::with(['customer', 'outlet', 'stylist', 'items']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transaction_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function ($c) {
                      $c->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterOutlet) {
            $query->where('outlet_id', $this->filterOutlet);
        }

        if ($this->filterPaymentMethod) {
            $query->where('payment_method', $this->filterPaymentMethod);
        }

        if ($this->dateFrom) {
            $query->whereDate('completed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('completed_at', '<=', $this->dateTo);
        }

        $transactions = $query->latest('completed_at')->paginate(10);
        $outlets = \App\Domains\Outlet\Models\Outlet::all();

        $selectedTransaction = $this->selectedTxId ? PosTransaction::with(['items', 'customer', 'outlet', 'stylist'])->find($this->selectedTxId) : null;

        return view('livewire.admin.transactions', compact('transactions', 'outlets', 'selectedTransaction'))
            ->layout('layouts.admin');
    }
}
