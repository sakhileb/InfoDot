<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Answer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question_id' => Questions::factory(),
            'content' => fake()->paragraph(),
            'is_accepted' => false,
        ];
    }

    /**
     * Indicate that the answer is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_accepted' => true,
        ]);
    }
}
