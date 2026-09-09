<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use App\Services\AccountDeletionService;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account permanently.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
            'confirmation' => ['required', 'string', 'in:HAPUS AKUN,hapus akun'],
        ], [
            'password.required' => 'Kata sandi saat ini wajib diisi untuk verifikasi keamanan.',
            'password.current_password' => 'Kata sandi yang Anda masukkan salah.',
            'confirmation.in' => 'Ketik frasa "HAPUS AKUN" dengan tepat untuk mengonfirmasi penghapusan permanen.',
            'confirmation.required' => 'Ketik frasa konfirmasi "HAPUS AKUN".',
        ]);

        $user = $request->user();
        $deletionReason = $request->input('reason', 'Penghapusan akun mandiri oleh pengguna');

        try {
            if ($user->isStylist()) {
                AccountDeletionService::deleteStylistAccount($user, $deletionReason);
            } elseif ($user->isSuperAdmin() || $user->isOutletAdmin()) {
                AccountDeletionService::deleteAdminAccount($user, $deletionReason);
            } else {
                AccountDeletionService::deleteCustomerAccount($user, $deletionReason);
            }
        } catch (ValidationException $e) {
            return Redirect::back()->withErrors($e->errors(), 'userDeletion');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('message', 'Akun Anda telah berhasil dihapus secara permanen dari sistem MORE Hair Studio.');
    }
}
