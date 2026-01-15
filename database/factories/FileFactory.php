<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::factory(),
            'team_id' => null,
            'name' => $this->faker->words(3, true),
            'path' => 'files/' . $this->faker->uuid . '.pdf',
            'size' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
