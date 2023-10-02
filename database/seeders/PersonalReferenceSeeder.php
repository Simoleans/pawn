<?php

namespace Database\Seeders;

use App\Models\PersonalReference;
use Illuminate\Database\Seeder;

class PersonalReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PersonalReference::factory(60)->create();
    }
}
