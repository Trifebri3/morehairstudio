<?php

namespace App\Services;

use App\Domains\Booking\Models\Booking;
use App\Domains\Customer\Models\Customer;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\System\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountDeletionService
{
    /**
     * Delete / Anonymize a customer account according to UU PDP No. 27/2022 and GDPR standard.
     * Retains transaction financial records while purging all Personally Identifiable Information (PII).
     */
    public static function deleteCustomerAccount(Customer|User $entity, ?string $reason = null): array
    {
        return DB::transaction(function () use ($entity, $reason) {
            $user = null;
            $customer = null;

            if ($entity instanceof User) {
                $user = $entity;
                // Find associated customer by email or phone
                $customer = Customer::where('email', $user->email)
                    ->orWhere('phone', $user->phone ?? '')
                    ->first();
            } else {
                $customer = $entity;
                if ($customer->email) {
                    $user = User::where('email', $customer->email)->first();
                }
            }

            $affectedBookingsCount = 0;
            $anonymizedCustomerCode = null;

            // 1. Handle Customer Record if exists
            if ($customer) {
                $originalPhone = $customer->phone;
                $originalEmail = $customer->email;
                $originalName = $customer->name;
                $anonymizedCustomerCode = $customer->customer_code;

                // Cancel future / active unfulfilled bookings
                $activeBookings = Booking::where('customer_id', $customer->id)
                    ->whereIn('status', ['pending', 'confirmed', 'waiting_checkin'])
                    ->get();

                foreach ($activeBookings as $b) {
                    $b->status = 'cancelled';
                    $b->notes = trim(($b->notes ?? '') . ' [Dibatalkan otomatis: Akun pelanggan dihapus secara permanen]');
                    $b->save();
                    $affectedBookingsCount++;
                }

                // Anonymize all Personally Identifiable Information (PII)
                // Keep customer row so historical completed bookings/payments (cascadeOnDelete) are not destroyed!
                $customer->update([
                    'name' => 'Pelanggan Terhapus (Anonim)',
                    'phone' => 'DEL-' . $customer->id . '-' . strtoupper(Str::random(6)),
                    'whatsapp_phone' => null,
                    'email' => null,
                    'birth_date' => null,
                    'gender' => null,
                    'address' => null,
                    'notes' => 'Akun dihapus permanen oleh pengguna/admin pada ' . now()->toDateTimeString() . ($reason ? ' (Alasan: ' . $reason . ')' : ''),
                    'tags' => null,
                    'loyalty_points' => 0,
                    'status' => 'deleted',
                    'whatsapp_marketing_opt_in' => false,
                    'email_marketing_opt_in' => false,
                    'acquisition_metadata' => null,
                ]);

                AuditLogger::log(
                    action: 'customer_account_deleted_anonymized',
                    modelType: Customer::class,
                    modelId: $customer->id,
                    oldValues: [
                        'code' => $anonymizedCustomerCode,
                        'name' => $originalName,
                        'phone_masked' => substr($originalPhone, 0, 4) . '****',
                        'email_masked' => $originalEmail ? substr($originalEmail, 0, 3) . '***@***' : null
                    ],
                    newValues: [
                        'status' => 'deleted',
                        'reason' => $reason,
                        'cancelled_bookings_count' => $affectedBookingsCount
                    ]
                );
            }

            // 2. Handle User Login Account if exists
            if ($user) {
                $userEmail = $user->email;
                $userId = $user->id;

                AuditLogger::log(
                    action: 'user_account_deleted',
                    modelType: User::class,
                    modelId: $userId,
                    oldValues: ['email' => $userEmail, 'role' => $user->role],
                    newValues: ['status' => 'deleted']
                );

                $user->delete();
            }

            return [
                'status' => 'success',
                'customer_code' => $anonymizedCustomerCode,
                'cancelled_bookings' => $affectedBookingsCount,
                'user_deleted' => (bool) $user
            ];
        });
    }

    /**
     * Delete / Deactivate a Barber (Stylist) account.
     * Prevents deletion if there are active / pending upcoming bookings.
     */
    public static function deleteStylistAccount(User|Stylist $entity, ?string $reason = null): array
    {
        return DB::transaction(function () use ($entity, $reason) {
            $user = null;
            $stylist = null;

            if ($entity instanceof User) {
                $user = $entity;
                $stylist = Stylist::where('user_id', $user->id)->first();
            } else {
                $stylist = $entity;
                if ($stylist->user_id) {
                    $user = User::find($stylist->user_id);
                }
            }

            // Safety Guard: Check if Stylist has active / pending upcoming bookings
            if ($stylist) {
                $activeBookings = Booking::where('stylist_id', $stylist->id)
                    ->whereIn('status', ['pending', 'confirmed', 'waiting_checkin', 'in_progress'])
                    ->where('booking_date', '>=', now()->toDateString())
                    ->count();

                if ($activeBookings > 0) {
                    throw ValidationException::withMessages([
                        'stylist' => [
                            "Stylist '{$stylist->name}' masih memiliki {$activeBookings} reservasi aktif/mendatang. Harap batalkan atau alihkan reservasi tersebut ke stylist lain sebelum menghapus akun."
                        ]
                    ]);
                }

                $stylistName = $stylist->name;
                $stylistId = $stylist->id;

                // Clean up photo file if exists
                if ($stylist->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($stylist->photo_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($stylist->photo_path);
                }

                // Delete the Stylist record permanently from the database
                // Foreign keys on bookings and reviews have nullOnDelete(), preserving historical transactions
                $stylist->delete();

                AuditLogger::log(
                    action: 'stylist_deleted_permanently',
                    modelType: Stylist::class,
                    modelId: $stylistId,
                    oldValues: ['name' => $stylistName],
                    newValues: ['status' => 'deleted', 'reason' => $reason]
                );
            }

            // Delete the login user account permanently
            if ($user) {
                $userId = $user->id;
                $userEmail = $user->email;

                AuditLogger::log(
                    action: 'user_account_deleted',
                    modelType: User::class,
                    modelId: $userId,
                    oldValues: ['email' => $userEmail, 'role' => 'stylist'],
                    newValues: ['status' => 'deleted']
                );

                $user->delete();
            }

            return [
                'status' => 'success',
                'stylist_name' => $stylist?->name,
                'user_deleted' => (bool) $user
            ];
        });
    }

    /**
     * Delete an Admin (Super Admin or Outlet Admin) account.
     * Prevents deletion of the system's last remaining Super Admin.
     */
    public static function deleteAdminAccount(User $user, ?string $reason = null): array
    {
        return DB::transaction(function () use ($user, $reason) {
            // Safety Guard: Last Super Admin Protection
            if ($user->isSuperAdmin()) {
                $superAdminCount = User::where('role', 'super_admin')->count();
                if ($superAdminCount <= 1) {
                    throw ValidationException::withMessages([
                        'user' => ['Aksi ditolak: Sistem harus memiliki setidaknya satu Super Admin aktif. Anda tidak dapat menghapus Super Admin terakhir.']
                    ]);
                }
            }

            $userId = $user->id;
            $userEmail = $user->email;
            $userRole = $user->role;

            AuditLogger::log(
                action: 'admin_account_deleted',
                modelType: User::class,
                modelId: $userId,
                oldValues: ['name' => $user->name, 'email' => $userEmail, 'role' => $userRole],
                newValues: ['status' => 'deleted', 'reason' => $reason]
            );

            $user->delete();

            return [
                'status' => 'success',
                'deleted_user_id' => $userId,
                'email' => $userEmail,
                'role' => $userRole
            ];
        });
    }
}
