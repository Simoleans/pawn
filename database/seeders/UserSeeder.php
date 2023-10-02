<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super_admin@system.com',
        ]);

		$adminRole = Role::query()->first();

		$admin->assignRole($adminRole);
        // \App\Models\User::factory(10)->create();
    }
}
