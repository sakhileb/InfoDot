<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSetupVerificationTest extends TestCase
{
    /**
     * Test that all required tables exist in the database.
     */
    public function test_all_required_tables_exist(): void
    {
        $requiredTables = [
            'users', 'password_resets', 'failed_jobs', 'personal_access_tokens',
            'sessions', 'teams', 'team_user', 'team_invitations',
            'followers', 'questions', 'answers', 'solutions', 'steps',
            'likes', 'comments', 'associates', 'files', 'folders', 'objs',
            'media', 'websockets_statistics_entries', 'migrations'
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' does not exist in the database"
            );
        }
    }

    /**
     * Test that foreign keys exist on the answers table.
     */
    public function test_answers_table_has_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('answers', 'user_id'));
        $this->assertTrue(Schema::hasColumn('answers', 'question_id'));

        // Check foreign keys exist
        $foreignKeys = $this->getForeignKeys('answers');
        
        $this->assertNotEmpty($foreignKeys, 'Answers table should have foreign keys');
        
        $foreignKeyColumns = array_column($foreignKeys, 'COLUMN_NAME');
        $this->assertContains('user_id', $foreignKeyColumns);
        $this->assertContains('question_id', $foreignKeyColumns);
    }

    /**
     * Test that foreign keys exist on the questions table.
     */
    public function test_questions_table_has_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('questions', 'user_id'));

        $foreignKeys = $this->getForeignKeys('questions');
        
        $this->assertNotEmpty($foreignKeys, 'Questions table should have foreign keys');
        
        $foreignKeyColumns = array_column($foreignKeys, 'COLUMN_NAME');
        $this->assertContains('user_id', $foreignKeyColumns);
    }

    /**
     * Test that foreign keys exist on the solutions table.
     */
    public function test_solutions_table_has_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('solutions', 'user_id'));

        $foreignKeys = $this->getForeignKeys('solutions');
        
        $this->assertNotEmpty($foreignKeys, 'Solutions table should have foreign keys');
        
        $foreignKeyColumns = array_column($foreignKeys, 'COLUMN_NAME');
        $this->assertContains('user_id', $foreignKeyColumns);
    }

    /**
     * Test that foreign keys exist on the steps table.
     */
    public function test_steps_table_has_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('steps', 'user_id'));
        $this->assertTrue(Schema::hasColumn('steps', 'solution_id'));

        $foreignKeys = $this->getForeignKeys('steps');
        
        $this->assertNotEmpty($foreignKeys, 'Steps table should have foreign keys');
        
        $foreignKeyColumns = array_column($foreignKeys, 'COLUMN_NAME');
        $this->assertContains('user_id', $foreignKeyColumns);
        $this->assertContains('solution_id', $foreignKeyColumns);
    }

    /**
     * Test cascade delete: User deletion cascades to questions and answers.
     */
    public function test_user_deletion_cascades_to_questions_and_answers(): void
    {
        DB::beginTransaction();

        try {
            // Create test user
            $userId = DB::table('users')->insertGetId([
                'name' => 'Test User Cascade',
                'email' => 'cascade_test_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create test question
            $questionId = DB::table('questions')->insertGetId([
                'user_id' => $userId,
                'question' => 'Test Question for Cascade',
                'description' => 'Testing cascade delete',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create test answer
            $answerId = DB::table('answers')->insertGetId([
                'user_id' => $userId,
                'question_id' => $questionId,
                'content' => 'Test Answer',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Verify records exist
            $this->assertTrue(DB::table('users')->where('id', $userId)->exists());
            $this->assertTrue(DB::table('questions')->where('id', $questionId)->exists());
            $this->assertTrue(DB::table('answers')->where('id', $answerId)->exists());

            // Delete user
            DB::table('users')->where('id', $userId)->delete();

            // Verify cascade delete worked
            $this->assertFalse(
                DB::table('questions')->where('id', $questionId)->exists(),
                'Question should be deleted when user is deleted (cascade)'
            );
            $this->assertFalse(
                DB::table('answers')->where('id', $answerId)->exists(),
                'Answer should be deleted when user is deleted (cascade)'
            );

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test cascade delete: Question deletion cascades to answers.
     */
    public function test_question_deletion_cascades_to_answers(): void
    {
        DB::beginTransaction();

        try {
            // Create test user
            $userId = DB::table('users')->insertGetId([
                'name' => 'Test User 2',
                'email' => 'cascade_test2_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create test question
            $questionId = DB::table('questions')->insertGetId([
                'user_id' => $userId,
                'question' => 'Test Question 2',
                'description' => 'Testing cascade delete 2',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create test answer
            $answerId = DB::table('answers')->insertGetId([
                'user_id' => $userId,
                'question_id' => $questionId,
                'content' => 'Test Answer 2',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Verify records exist
            $this->assertTrue(DB::table('questions')->where('id', $questionId)->exists());
            $this->assertTrue(DB::table('answers')->where('id', $answerId)->exists());

            // Delete question
            DB::table('questions')->where('id', $questionId)->delete();

            // Verify cascade delete worked
            $this->assertFalse(
                DB::table('answers')->where('id', $answerId)->exists(),
                'Answer should be deleted when question is deleted (cascade)'
            );

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test cascade delete: Solution deletion cascades to steps.
     */
    public function test_solution_deletion_cascades_to_steps(): void
    {
        DB::beginTransaction();

        try {
            // Create test user
            $userId = DB::table('users')->insertGetId([
                'name' => 'Test User 3',
                'email' => 'cascade_test3_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create test solution
            $solutionId = DB::table('solutions')->insertGetId([
                'user_id' => $userId,
                'solution_title' => 'Test Solution',
                'solution_description' => 'Testing cascade',
                'tags' => 'test',
                'duration' => 1,
                'duration_type' => 'days',
                'steps' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create test step
            $stepId = DB::table('steps')->insertGetId([
                'user_id' => $userId,
                'solution_id' => $solutionId,
                'solution_heading' => 'Test Step',
                'solution_body' => 'Test step body',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Verify records exist
            $this->assertTrue(DB::table('solutions')->where('id', $solutionId)->exists());
            $this->assertTrue(DB::table('steps')->where('id', $stepId)->exists());

            // Delete solution
            DB::table('solutions')->where('id', $solutionId)->delete();

            // Verify cascade delete worked
            $this->assertFalse(
                DB::table('steps')->where('id', $stepId)->exists(),
                'Step should be deleted when solution is deleted (cascade)'
            );

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test that FULLTEXT index exists on questions table.
     */
    public function test_questions_table_has_fulltext_index(): void
    {
        $indexes = DB::select("SHOW INDEX FROM questions WHERE Index_type = 'FULLTEXT'");
        
        $this->assertNotEmpty($indexes, 'Questions table should have FULLTEXT index');
        
        $indexedColumns = array_unique(array_column($indexes, 'Column_name'));
        $this->assertContains('question', $indexedColumns);
        $this->assertContains('description', $indexedColumns);
    }

    /**
     * Test that FULLTEXT index exists on solutions table.
     */
    public function test_solutions_table_has_fulltext_index(): void
    {
        $indexes = DB::select("SHOW INDEX FROM solutions WHERE Index_type = 'FULLTEXT'");
        
        $this->assertNotEmpty($indexes, 'Solutions table should have FULLTEXT index');
        
        $indexedColumns = array_unique(array_column($indexes, 'Column_name'));
        $this->assertContains('solution_title', $indexedColumns);
        $this->assertContains('solution_description', $indexedColumns);
        $this->assertContains('tags', $indexedColumns);
    }

    /**
     * Test that FULLTEXT search works on questions table.
     */
    public function test_fulltext_search_works_on_questions(): void
    {
        DB::beginTransaction();

        try {
            // Create test user
            $userId = DB::table('users')->insertGetId([
                'name' => 'Search Test User',
                'email' => 'search_test_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert test questions
            $laravelQuestionId = DB::table('questions')->insertGetId([
                'user_id' => $userId,
                'question' => 'How to use Laravel Eloquent?',
                'description' => 'I need help with Laravel Eloquent ORM',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('questions')->insert([
                'user_id' => $userId,
                'question' => 'JavaScript async await',
                'description' => 'Understanding async await in JavaScript',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Test FULLTEXT search
            $results = DB::select("
                SELECT id, question, description
                FROM questions
                WHERE MATCH(question, description) AGAINST('Laravel' IN NATURAL LANGUAGE MODE)
            ");

            $this->assertNotEmpty($results, 'FULLTEXT search should return results');
            $this->assertEquals($laravelQuestionId, $results[0]->id);

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test that FULLTEXT search works on solutions table.
     */
    public function test_fulltext_search_works_on_solutions(): void
    {
        DB::beginTransaction();

        try {
            // Create test user
            $userId = DB::table('users')->insertGetId([
                'name' => 'Solution Search Test',
                'email' => 'solution_search_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert test solutions
            $laravelSolutionId = DB::table('solutions')->insertGetId([
                'user_id' => $userId,
                'solution_title' => 'Deploy Laravel Application',
                'solution_description' => 'Step by step guide to deploy Laravel',
                'tags' => 'laravel,deployment',
                'duration' => 1,
                'duration_type' => 'days',
                'steps' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('solutions')->insert([
                'user_id' => $userId,
                'solution_title' => 'Setup React Project',
                'solution_description' => 'How to setup a React project from scratch',
                'tags' => 'react,javascript',
                'duration' => 1,
                'duration_type' => 'hours',
                'steps' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Test FULLTEXT search
            $results = DB::select("
                SELECT id, solution_title, solution_description, tags
                FROM solutions
                WHERE MATCH(solution_title, solution_description, tags) AGAINST('Laravel' IN NATURAL LANGUAGE MODE)
            ");

            $this->assertNotEmpty($results, 'FULLTEXT search should return results');
            $this->assertEquals($laravelSolutionId, $results[0]->id);

        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test that key columns exist in users table.
     */
    public function test_users_table_has_required_columns(): void
    {
        $requiredColumns = [
            'id', 'name', 'email', 'password', 'email_verified_at',
            'two_factor_secret', 'current_team_id', 'profile_photo_path',
            'created_at', 'updated_at'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Users table should have column '{$column}'"
            );
        }
    }

    /**
     * Test that key columns exist in questions table.
     */
    public function test_questions_table_has_required_columns(): void
    {
        $requiredColumns = [
            'id', 'user_id', 'question', 'description', 'tags', 'status',
            'created_at', 'updated_at'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('questions', $column),
                "Questions table should have column '{$column}'"
            );
        }
    }

    /**
     * Test that key columns exist in answers table.
     */
    public function test_answers_table_has_required_columns(): void
    {
        $requiredColumns = [
            'id', 'user_id', 'question_id', 'content', 'is_accepted',
            'created_at', 'updated_at'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('answers', $column),
                "Answers table should have column '{$column}'"
            );
        }
    }

    /**
     * Test that key columns exist in solutions table.
     */
    public function test_solutions_table_has_required_columns(): void
    {
        $requiredColumns = [
            'id', 'user_id', 'solution_title', 'solution_description',
            'tags', 'duration', 'duration_type', 'steps',
            'created_at', 'updated_at'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('solutions', $column),
                "Solutions table should have column '{$column}'"
            );
        }
    }

    /**
     * Test that key columns exist in likes table (polymorphic).
     */
    public function test_likes_table_has_polymorphic_columns(): void
    {
        $requiredColumns = [
            'id', 'user_id', 'likable_type', 'likable_id', 'like',
            'created_at', 'updated_at'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('likes', $column),
                "Likes table should have column '{$column}'"
            );
        }
    }

    /**
     * Test that key columns exist in comments table (polymorphic).
     */
    public function test_comments_table_has_polymorphic_columns(): void
    {
        $requiredColumns = [
            'id', 'user_id', 'commentable_type', 'commentable_id', 'body',
            'created_at', 'updated_at'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('comments', $column),
                "Comments table should have column '{$column}'"
            );
        }
    }

    /**
     * Helper method to get foreign keys for a table.
     */
    private function getForeignKeys(string $table): array
    {
        return DB::select("
            SELECT 
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]);
    }
}
