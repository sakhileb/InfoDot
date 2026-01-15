<?php

namespace Database\Factories;

use App\Models\Questions;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Questions>
 */
class QuestionsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Questions::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question' => fake()->sentence() . '?',
            'description' => fake()->paragraph(),
            'tags' => implode(',', fake()->words(3)),
            'status' => fake()->randomElement(['open', 'closed', 'answered']),
        ];
    }
}
