<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndUsersSeeder extends Seeder
{
    public function run()
    {
        // Tạo vai trò
        $salerRole = Role::create(['name' => 'saler']);
        $quoteManagerRole = Role::create(['name' => 'quote manager']);
        // Tạo người dùng bộ phận báo giá
        $manager = User::find(1);
        $manager->assignRole($quoteManagerRole);
    }
}
