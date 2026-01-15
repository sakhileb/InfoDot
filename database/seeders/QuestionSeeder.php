<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
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

        $questions = [
            [
                'question' => 'How to implement authentication in Laravel 11?',
                'description' => 'I am trying to implement user authentication in my Laravel 11 application. What is the best approach to use? Should I use Jetstream, Breeze, or build a custom solution?',
                'tags' => 'laravel,authentication,security',
                'status' => 1,
            ],
            [
                'question' => 'What is the difference between Livewire and Vue.js?',
                'description' => 'I am confused about when to use Livewire versus Vue.js in a Laravel application. Can someone explain the key differences and use cases for each?',
                'tags' => 'livewire,vue,frontend',
                'status' => 1,
            ],
            [
                'question' => 'How to optimize database queries in Laravel?',
                'description' => 'My application is experiencing slow performance due to N+1 query problems. What are the best practices for optimizing database queries in Laravel?',
                'tags' => 'laravel,database,performance,optimization',
                'status' => 1,
            ],
            [
                'question' => 'Best practices for API development in Laravel?',
                'description' => 'I am building a RESTful API using Laravel. What are the best practices for structuring the API, handling authentication, and versioning?',
                'tags' => 'laravel,api,rest,sanctum',
                'status' => 1,
            ],
            [
                'question' => 'How to implement real-time notifications?',
                'description' => 'I want to add real-time notifications to my Laravel application. Should I use Laravel Reverb, Pusher, or another solution?',
                'tags' => 'laravel,real-time,notifications,reverb',
                'status' => 1,
            ],
            [
                'question' => 'How to handle file uploads in Laravel?',
                'description' => 'What is the best way to handle file uploads in Laravel? I need to support images, documents, and videos with validation and storage management.',
                'tags' => 'laravel,file-upload,storage',
                'status' => 1,
            ],
            [
                'question' => 'Testing strategies for Laravel applications?',
                'description' => 'What are the recommended testing strategies for Laravel applications? How should I structure my tests and what should I test?',
                'tags' => 'laravel,testing,phpunit',
                'status' => 1,
            ],
            [
                'question' => 'How to implement search functionality?',
                'description' => 'I need to add search functionality to my Laravel application. Should I use Laravel Scout with Meilisearch, Algolia, or MySQL FULLTEXT search?',
                'tags' => 'laravel,search,scout,meilisearch',
                'status' => 1,
            ],
            [
                'question' => 'Deployment best practices for Laravel?',
                'description' => 'What are the best practices for deploying a Laravel application to production? What should I consider for security, performance, and reliability?',
                'tags' => 'laravel,deployment,production,devops',
                'status' => 1,
            ],
            [
                'question' => 'How to use queues in Laravel?',
                'description' => 'I want to implement background job processing in my Laravel application. How do I set up and use queues effectively?',
                'tags' => 'laravel,queues,jobs,redis',
                'status' => 1,
            ],
        ];

        foreach ($questions as $questionData) {
            DB::table('questions')->insert([
                'user_id' => $users->random()->id,
                'question' => $questionData['question'],
                'description' => $questionData['description'],
                'tags' => $questionData['tags'],
                'status' => $questionData['status'],
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('Created ' . count($questions) . ' sample questions.');
    }
}
