<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $solutions = [
            [
                'solution_title' => 'Setting up Laravel 11 with Jetstream',
                'solution_description' => 'A complete guide to setting up a new Laravel 11 project with Jetstream authentication scaffolding.',
                'tags' => 'laravel,jetstream,setup,authentication',
                'duration' => 2,
                'duration_type' => 'hours',
                'steps' => [
                    [
                        'heading' => 'Install Laravel',
                        'body' => 'Run composer create-project laravel/laravel project-name to create a new Laravel project.',
                    ],
                    [
                        'heading' => 'Install Jetstream',
                        'body' => 'Run composer require laravel/jetstream and then php artisan jetstream:install livewire --teams.',
                    ],
                    [
                        'heading' => 'Configure Database',
                        'body' => 'Update your .env file with database credentials and run php artisan migrate.',
                    ],
                    [
                        'heading' => 'Install Frontend Dependencies',
                        'body' => 'Run npm install && npm run build to compile frontend assets.',
                    ],
                ],
            ],
            [
                'solution_title' => 'Implementing Search with Laravel Scout',
                'solution_description' => 'Learn how to add full-text search to your Laravel application using Scout and Meilisearch.',
                'tags' => 'laravel,scout,search,meilisearch',
                'duration' => 3,
                'duration_type' => 'hours',
                'steps' => [
                    [
                        'heading' => 'Install Scout',
                        'body' => 'Run composer require laravel/scout to install Laravel Scout.',
                    ],
                    [
                        'heading' => 'Install Meilisearch Driver',
                        'body' => 'Run composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle.',
                    ],
                    [
                        'heading' => 'Configure Scout',
                        'body' => 'Publish Scout config with php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider" and set SCOUT_DRIVER=meilisearch in .env.',
                    ],
                    [
                        'heading' => 'Add Searchable Trait',
                        'body' => 'Add the Searchable trait to your models and define the toSearchableArray() method.',
                    ],
                    [
                        'heading' => 'Import Data',
                        'body' => 'Run php artisan scout:import "App\Models\YourModel" to index existing data.',
                    ],
                ],
            ],
            [
                'solution_title' => 'Optimizing Laravel Database Queries',
                'solution_description' => 'Best practices for optimizing database queries and preventing N+1 problems in Laravel applications.',
                'tags' => 'laravel,database,optimization,performance',
                'duration' => 1,
                'duration_type' => 'days',
                'steps' => [
                    [
                        'heading' => 'Use Eager Loading',
                        'body' => 'Use the with() method to eager load relationships: Model::with(\'relation\')->get().',
                    ],
                    [
                        'heading' => 'Add Database Indexes',
                        'body' => 'Add indexes to frequently queried columns in your migrations using $table->index(\'column_name\').',
                    ],
                    [
                        'heading' => 'Use Query Caching',
                        'body' => 'Cache expensive queries using Cache::remember() to reduce database load.',
                    ],
                    [
                        'heading' => 'Optimize with Select',
                        'body' => 'Only select the columns you need: Model::select(\'id\', \'name\')->get().',
                    ],
                    [
                        'heading' => 'Use Chunk for Large Datasets',
                        'body' => 'Process large datasets in chunks: Model::chunk(100, function($records) { ... }).',
                    ],
                ],
            ],
            [
                'solution_title' => 'Building a RESTful API with Laravel',
                'solution_description' => 'Complete guide to building a secure and scalable RESTful API using Laravel and Sanctum.',
                'tags' => 'laravel,api,rest,sanctum,authentication',
                'duration' => 1,
                'duration_type' => 'weeks',
                'steps' => [
                    [
                        'heading' => 'Install Sanctum',
                        'body' => 'Run composer require laravel/sanctum and php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider".',
                    ],
                    [
                        'heading' => 'Create API Routes',
                        'body' => 'Define your API routes in routes/api.php with proper middleware.',
                    ],
                    [
                        'heading' => 'Create API Resources',
                        'body' => 'Use php artisan make:resource ResourceName to create API resource classes for data transformation.',
                    ],
                    [
                        'heading' => 'Implement Authentication',
                        'body' => 'Set up token-based authentication using Sanctum middleware.',
                    ],
                    [
                        'heading' => 'Add Rate Limiting',
                        'body' => 'Configure rate limiting in app/Http/Kernel.php to protect your API.',
                    ],
                    [
                        'heading' => 'Write API Tests',
                        'body' => 'Create feature tests for all API endpoints to ensure reliability.',
                    ],
                ],
            ],
            [
                'solution_title' => 'Implementing Real-time Features with Laravel Reverb',
                'solution_description' => 'Add real-time functionality to your Laravel application using the new Laravel Reverb WebSocket server.',
                'tags' => 'laravel,reverb,websockets,real-time,broadcasting',
                'duration' => 5,
                'duration_type' => 'hours',
                'steps' => [
                    [
                        'heading' => 'Install Reverb',
                        'body' => 'Run composer require laravel/reverb and php artisan reverb:install.',
                    ],
                    [
                        'heading' => 'Configure Broadcasting',
                        'body' => 'Update config/broadcasting.php and set BROADCAST_CONNECTION=reverb in .env.',
                    ],
                    [
                        'heading' => 'Create Events',
                        'body' => 'Create broadcastable events using php artisan make:event EventName.',
                    ],
                    [
                        'heading' => 'Start Reverb Server',
                        'body' => 'Run php artisan reverb:start to start the WebSocket server.',
                    ],
                    [
                        'heading' => 'Configure Laravel Echo',
                        'body' => 'Set up Laravel Echo on the frontend to listen for events.',
                    ],
                ],
            ],
        ];

        foreach ($solutions as $solutionData) {
            $userId = $users->random()->id;
            
            $solutionId = DB::table('solutions')->insertGetId([
                'user_id' => $userId,
                'solution_title' => $solutionData['solution_title'],
                'solution_description' => $solutionData['solution_description'],
                'tags' => $solutionData['tags'],
                'duration' => $solutionData['duration'],
                'duration_type' => $solutionData['duration_type'],
                'steps' => count($solutionData['steps']),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);

            // Create steps for this solution
            foreach ($solutionData['steps'] as $stepData) {
                DB::table('solutions_step')->insert([
                    'user_id' => $userId,
                    'solution_id' => $solutionId,
                    'solution_heading' => $stepData['heading'],
                    'solution_body' => $stepData['body'],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $this->command->info('Created ' . count($solutions) . ' sample solutions with steps.');
    }
}
