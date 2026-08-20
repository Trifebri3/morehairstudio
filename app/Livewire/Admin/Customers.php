<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Services\PhoneNormalizer;
use Illuminate\Support\Str;

class Customers extends Component
{
    use WithPagination;

    public $search = '';
    public $isEditing = false;
    public $customerId = null;

    // Form fields
    public $name = '';
    public $phone = '';
    public $email = '';
    public $birth_date = '';
    public $gender = '';
    public $address = '';
    public $tags = ''; // string comma-separated tags
    public $notes = '';
    public $first_acquisition_source = 'Website';

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        session()->flash('message', "Data customer {$customer->name} berhasil dihapus.");
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = true;
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->birth_date = $customer->birth_date ? $customer->birth_date->toDateString() : '';
        $this->gender = $customer->gender;
        $this->address = $customer->address;
        $this->tags = is_array($customer->tags) ? implode(', ', $customer->tags) : '';
        $this->notes = $customer->notes;
        $this->first_acquisition_source = $customer->first_acquisition_source ?: 'Website';
        $this->isEditing = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|min:9|unique:customers,phone,' . ($this->customerId ?? 'NULL'),
            'email' => 'nullable|email|max:255|unique:customers,email,' . ($this->customerId ?? 'NULL'),
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
            'first_acquisition_source' => 'required|string',
        ];

        $this->validate($rules);

        // Normalize phone number
        $normalizedPhone = PhoneNormalizer::normalize($this->phone);

        // Process tags array
        $tagsArray = $this->tags ? array_map('trim', explode(',', $this->tags)) : null;

        if ($this->customerId) {
            $customer = Customer::findOrFail($this->customerId);
            
            $oldValues = $customer->toArray();

            $customer->update([
                'name' => $this->name,
                'phone' => $normalizedPhone,
                'email' => $this->email,
                'birth_date' => $this->birth_date ?: null,
                'gender' => $this->gender ?: null,
                'address' => $this->address ?: null,
                'tags' => $tagsArray,
                'notes' => $this->notes ?: null,
                'first_acquisition_source' => $this->first_acquisition_source,
            ]);

            \App\Domains\System\Services\AuditLogger::log(
                'customer.update',
                Customer::class,
                $customer->id,
                $oldValues,
                $customer->fresh()->toArray()
            );

            session()->flash('message', "Data customer {$customer->name} berhasil diperbarui.");
        } else {
            // Generate unique customer code
            $code = 'CUST-' . strtoupper(Str::random(8));
            while (Customer::where('customer_code', $code)->exists()) {
                $code = 'CUST-' . strtoupper(Str::random(8));
            }

            $customer = Customer::create([
                'customer_code' => $code,
                'name' => $this->name,
                'phone' => $normalizedPhone,
                'email' => $this->email,
                'birth_date' => $this->birth_date ?: null,
                'gender' => $this->gender ?: null,
                'address' => $this->address ?: null,
                'tags' => $tagsArray,
                'notes' => $this->notes ?: null,
                'first_acquisition_source' => $this->first_acquisition_source,
                'latest_acquisition_source' => $this->first_acquisition_source,
            ]);

            // Log Customer activity
            \App\Domains\Customer\Models\CustomerActivity::create([
                'customer_id' => $customer->id,
                'event_type' => 'registered',
                'event_date' => Carbon::now(),
                'source' => 'dashboard',
                'reference_type' => Customer::class,
                'reference_id' => $customer->id,
                'metadata' => [
                    'customer_code' => $code,
                    'acquisition_source' => $this->first_acquisition_source
                ]
            ]);

            \App\Domains\System\Services\AuditLogger::log(
                'customer.create',
                Customer::class,
                $customer->id,
                null,
                $customer->toArray()
            );

            session()->flash('message', "Data customer {$customer->name} berhasil dibuat.");
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
        $this->customerId = null;
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->birth_date = '';
        $this->gender = '';
        $this->address = '';
        $this->tags = '';
        $this->notes = '';
        $this->first_acquisition_source = 'Website';
    }

    public function render()
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->route('dashboard');
        }

        $customers = Customer::where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_code', 'like', '%' . $this->search . '%');
            })
            ->withCount('bookings')
            ->paginate(10);

        return view('livewire.admin.customers', compact('customers'))->layout('layouts.admin');
    }
}
