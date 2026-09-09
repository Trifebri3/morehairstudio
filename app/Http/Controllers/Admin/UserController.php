<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Outlet\Models\Outlet;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('outlet')->latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $outlets = Outlet::all();

        return view('admin.users.create', compact('outlets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(['super_admin', 'outlet_admin', 'stylist'])],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'outlet_id' => $request->outlet_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $outlets = Outlet::all();

        return view('admin.users.edit', compact('user', 'outlets'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(['super_admin', 'outlet_admin', 'stylist'])],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'outlet_id' => $request->outlet_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri dari panel ini. Silakan gunakan menu Pengaturan Profil jika ingin menghapus akun Anda.');
        }

        try {
            if ($user->isStylist()) {
                AccountDeletionService::deleteStylistAccount($user, 'Dihapus oleh Super Admin dari Panel Users');
            } elseif ($user->isSuperAdmin() || $user->isOutletAdmin()) {
                AccountDeletionService::deleteAdminAccount($user, 'Dihapus oleh Super Admin dari Panel Users');
            } else {
                AccountDeletionService::deleteCustomerAccount($user, 'Dihapus oleh Super Admin dari Panel Users');
            }

            return redirect()->route('admin.users.index')->with('success', "Akun '{$user->name}' berhasil dihapus secara aman.");
        } catch (ValidationException $e) {
            $errorMsg = collect($e->errors())->flatten()->first() ?? 'Gagal memproses penghapusan user.';
            return redirect()->route('admin.users.index')->with('error', $errorMsg);
        }
    }
}
