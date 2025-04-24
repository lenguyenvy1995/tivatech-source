<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo Permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage own quote requests',
            'manage all quote requests',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Gán Permissions cho Roles
        $adminRole = Role::findByName('admin');
        $adminRole->syncPermissions($permissions); // Admin có tất cả Permissions

        $salerRole = Role::findByName('saler');
        $salerRole->syncPermissions(['view dashboard', 'manage own quote requests']);

        $quoteManagerRole = Role::findByName('role-quote-manager');
        $quoteManagerRole->syncPermissions(['view dashboard', 'manage all quote requests']);
    }
}
