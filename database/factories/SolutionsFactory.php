<?php

namespace Database\Factories;

use App\Models\Solutions;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Solutions>
 */
class SolutionsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Solutions::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'solution_title' => fake()->sentence(),
            'solution_description' => fake()->paragraph(),
            'tags' => implode(',', fake()->words(3)),
            'duration' => fake()->numberBetween(1, 30),
            'duration_type' => fake()->randomElement(['hours', 'days', 'weeks', 'months', 'years', 'infinite']),
            'steps' => null,
        ];
    }
}
