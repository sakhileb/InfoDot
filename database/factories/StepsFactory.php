<?php

namespace Database\Factories;

use App\Models\Steps;
use App\Models\Solutions;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Steps>
 */
class StepsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Steps::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'solution_id' => Solutions::factory(),
            'solution_heading' => fake()->sentence(),
            'solution_body' => fake()->paragraph(),
        ];
    }
}
