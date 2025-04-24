<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // // Tạo vai trò 'admin'
        // $adminRole = Role::create(['name' => 'admin']);

        // // Tạo vai trò 'saler'
        // $salerRole = Role::create(['name' => 'saler']);

        // Gán Permissions cho Roles
        $adminRole->givePermissionTo(['manage users', 'manage roles', 'view dashboard', 'manage quotes']);
        $salerRole->givePermissionTo(['view dashboard', 'manage quotes']);
    }
}
