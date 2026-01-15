<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $questions = DB::table('questions')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($questions->isEmpty()) {
            $this->command->warn('No questions found. Please run QuestionSeeder first.');
            return;
        }

        $answerTemplates = [
            'You can achieve this by using {solution}. Here is an example: {example}',
            'I recommend using {solution}. It provides {benefit} and is well-documented.',
            'The best approach is to {solution}. This will help you {benefit}.',
            'Have you tried {solution}? It worked well for me in a similar situation.',
            'I had the same issue. I solved it by {solution}. Hope this helps!',
            'Check out the official documentation for {solution}. It has great examples.',
            'You should consider {solution}. It is the recommended way to handle this.',
            'I suggest using {solution} because it offers better {benefit}.',
        ];

        $solutions = [
            'Laravel Jetstream',
            'eager loading with the with() method',
            'Laravel Scout with Meilisearch',
            'Laravel Sanctum for API authentication',
            'Laravel Reverb for WebSocket connections',
            'Spatie Media Library',
            'PHPUnit with feature tests',
            'Redis for queue management',
            'Laravel Forge for deployment',
            'database transactions',
        ];

        $benefits = [
            'performance',
            'security',
            'scalability',
            'maintainability',
            'flexibility',
            'ease of use',
            'better developer experience',
            'comprehensive features',
        ];

        $examples = [
            'User::with(\'posts\')->get()',
            '$user->posts()->create([...])',
            'Model::search($query)->get()',
            'Route::middleware(\'auth:sanctum\')',
            'broadcast(new EventName($data))',
        ];

        $count = 0;
        foreach ($questions as $question) {
            // Create 1-3 answers per question
            $numAnswers = rand(1, 3);
            
            for ($i = 0; $i < $numAnswers; $i++) {
                $template = $answerTemplates[array_rand($answerTemplates)];
                $content = str_replace(
                    ['{solution}', '{benefit}', '{example}'],
                    [
                        $solutions[array_rand($solutions)],
                        $benefits[array_rand($benefits)],
                        $examples[array_rand($examples)],
                    ],
                    $template
                );

                $isAccepted = ($i === 0 && rand(0, 1) === 1); // First answer has 50% chance of being accepted

                DB::table('answers')->insert([
                    'user_id' => $users->random()->id,
                    'question_id' => $question->id,
                    'content' => $content,
                    'is_accepted' => $isAccepted,
                    'created_at' => now()->subDays(rand(1, 25)),
                    'updated_at' => now()->subDays(rand(1, 25)),
                ]);

                $count++;
            }
        }

        $this->command->info("Created {$count} sample answers.");
    }
}
