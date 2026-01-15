<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $questions = DB::table('questions')->get();
        $answers = DB::table('answers')->get();
        $solutions = DB::table('solutions')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $count = 0;

        // Add likes to questions
        foreach ($questions as $question) {
            $numLikes = rand(0, 5);
            $likedUsers = $users->random(min($numLikes, $users->count()));
            
            foreach ($likedUsers as $user) {
                DB::table('likes')->insert([
                    'user_id' => $user->id,
                    'like' => rand(0, 1) === 1, // Random like or dislike
                    'likable_type' => 'App\\Models\\Questions',
                    'likable_id' => $question->id,
                    'created_at' => now()->subDays(rand(1, 20)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);
                $count++;
            }
        }

        // Add likes to answers
        foreach ($answers as $answer) {
            $numLikes = rand(0, 4);
            $likedUsers = $users->random(min($numLikes, $users->count()));
            
            foreach ($likedUsers as $user) {
                DB::table('likes')->insert([
                    'user_id' => $user->id,
                    'like' => rand(0, 1) === 1, // Random like or dislike
                    'likable_type' => 'App\\Models\\Answer',
                    'likable_id' => $answer->id,
                    'created_at' => now()->subDays(rand(1, 20)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);
                $count++;
            }
        }

        // Add likes to solutions
        foreach ($solutions as $solution) {
            $numLikes = rand(0, 6);
            $likedUsers = $users->random(min($numLikes, $users->count()));
            
            foreach ($likedUsers as $user) {
                DB::table('likes')->insert([
                    'user_id' => $user->id,
                    'like' => rand(0, 1) === 1, // Random like or dislike
                    'likable_type' => 'App\\Models\\Solutions',
                    'likable_id' => $solution->id,
                    'created_at' => now()->subDays(rand(1, 20)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);
                $count++;
            }
        }

        $this->command->info("Created {$count} sample likes.");
    }
}
