<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::create(['name' => 'super', 'guard_name' => 'web'], ['name' => 'trabajador', 'guard_name' => 'web']);

        User::create([
            'name' => 'super',
            'email' => 'super@gmail.com',
            'password' => bcrypt('password'),
        ])->assignRole('super');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
