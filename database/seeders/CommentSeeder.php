<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
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

        $commentTemplates = [
            'Great question! I had the same issue.',
            'Thanks for sharing this!',
            'This helped me a lot.',
            'Could you provide more details?',
            'Have you tried this approach?',
            'I agree with this solution.',
            'This is exactly what I was looking for.',
            'Interesting perspective!',
            'I would also recommend checking the documentation.',
            'This worked perfectly for me!',
        ];

        $count = 0;

        // Add comments to questions
        foreach ($questions as $question) {
            $numComments = rand(0, 3);
            
            for ($i = 0; $i < $numComments; $i++) {
                DB::table('comments')->insert([
                    'user_id' => $users->random()->id,
                    'body' => $commentTemplates[array_rand($commentTemplates)],
                    'commentable_type' => 'App\\Models\\Questions',
                    'commentable_id' => $question->id,
                    'created_at' => now()->subDays(rand(1, 20)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);
                $count++;
            }
        }

        // Add comments to answers
        foreach ($answers as $answer) {
            $numComments = rand(0, 2);
            
            for ($i = 0; $i < $numComments; $i++) {
                DB::table('comments')->insert([
                    'user_id' => $users->random()->id,
                    'body' => $commentTemplates[array_rand($commentTemplates)],
                    'commentable_type' => 'App\\Models\\Answer',
                    'commentable_id' => $answer->id,
                    'created_at' => now()->subDays(rand(1, 20)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);
                $count++;
            }
        }

        // Add comments to solutions
        foreach ($solutions as $solution) {
            $numComments = rand(0, 4);
            
            for ($i = 0; $i < $numComments; $i++) {
                DB::table('comments')->insert([
                    'user_id' => $users->random()->id,
                    'body' => $commentTemplates[array_rand($commentTemplates)],
                    'commentable_type' => 'App\\Models\\Solutions',
                    'commentable_id' => $solution->id,
                    'created_at' => now()->subDays(rand(1, 20)),
                    'updated_at' => now()->subDays(rand(1, 20)),
                ]);
                $count++;
            }
        }

        $this->command->info("Created {$count} sample comments.");
    }
}
