<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'list users']);
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'update users']);
        Permission::create(['name' => 'delete users']);

        $allPermissions = Permission::all();
        $superAdminRole = Role::create(['name' => 'super admin']);
        $superAdminRole->givePermissionTo($allPermissions);

        $usersModulePermissions = Permission::query()->where('name', 'LIKE', "%users")->get();
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($usersModulePermissions);
    }
}
