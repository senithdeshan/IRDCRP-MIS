<?php

namespace Database\Seeders;

use App\Http\Controllers\StaffController;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'admin@irdcrp.lk',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('irdcrp@123'),
            'role' => 'super_admin',
            'status' => 'active',
            'designation' => 'System Administrator',
            'phone' => null,
            'permissions' => array_keys(StaffController::PERMISSIONS),
        ]);
    }
}
