<?php

namespace Database\Factories;

use App\Enums\Condition;
use App\Models\Category;
use App\Models\Client;
use App\States\ItemState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => random_int(1, Client::query()->count()),
            'category_id' => random_int(1, Category::query()->count()),
            'branch_id' => 1,
            'name' => $this->faker->sentence(2, true),
            'description' => $this->faker->sentence(10),
            'condition' => Arr::random(array_keys(Condition::toArray())),
            'estimated_value' => $this->faker->randomFloat(2, 100, 10000),
            'state' => ItemState::PENDING,
        ];
    }
}
