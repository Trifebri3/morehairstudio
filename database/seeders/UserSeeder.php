<?php

namespace Database\Seeders;

use App\Domains\Outlet\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        User::firstOrCreate([
            'email' => 'admin@morehair.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'outlet_id' => null,
        ]);

        // 2. Outlet Admins
        $bandung = Outlet::where('slug', 'more-hair-studio-bandung')->first();
        if ($bandung) {
            User::firstOrCreate([
                'email' => 'bandung@morehair.com',
            ], [
                'name' => 'Admin Bandung',
                'password' => Hash::make('password'),
                'role' => 'outlet_admin',
                'outlet_id' => $bandung->id,
            ]);
        }

        $jakarta = Outlet::where('slug', 'more-hair-studio-jakarta')->first();
        if ($jakarta) {
            User::firstOrCreate([
                'email' => 'jakarta@morehair.com',
            ], [
                'name' => 'Admin Jakarta',
                'password' => Hash::make('password'),
                'role' => 'outlet_admin',
                'outlet_id' => $jakarta->id,
            ]);
        }

        $this->command->info('Users seeded successfully!');
    }
}
