<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'outlet_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isOutletAdmin(): bool
    {
        return $this->role === 'outlet_admin';
    }

    public function isStylist(): bool
    {
        return $this->role === 'stylist';
    }

    public function outlet()
    {
        return $this->belongsTo(\App\Domains\Outlet\Models\Outlet::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        if (config('app.env') === 'local') {
            session()->flash('password_reset_token_dev', $token);
        }

        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
