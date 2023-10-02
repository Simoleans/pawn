<?php

namespace Database\Factories;

use App\Enums\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $maskDocument = Arr::random(['########', '#########']);

        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'document' => $this->faker->numerify($maskDocument),
            'issued' => Arr::random(array_keys(Department::toArray())),
            'address' => $this->faker->address,
            'address_references' => $this->faker->optional()->sentence,
            'mobile' => '+591 7' . $this->faker->numerify('## ####'),
            'phone' => '+591 ' . $this->faker->numerify('2 ### ####'),
            'email' => $this->faker->optional()->safeEmail,
        ];
    }
}
