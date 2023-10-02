<?php

namespace Database\Factories;

use App\Enums\Relationship;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonalReference>
 */
class PersonalReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::query()->inRandomOrder()->first()->id,
            'full_name' => $this->faker->name,
            'mobile' => '+591 7' . $this->faker->numerify('## ####'),
            'relationship' => Arr::random(array_keys(Relationship::toArray())),
            'address' => $this->faker->address,
        ];
    }
}
