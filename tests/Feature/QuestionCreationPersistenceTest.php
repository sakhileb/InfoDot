<?php

namespace Tests\Feature;

use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test: Question Creation Persistence
 * 
 * Feature: infodot-modernization, Property 2: Question Creation Persistence
 * Validates: Requirements FR-2
 * 
 * Property: For any valid question data, creating a question should result in 
 * a database record that can be retrieved with identical content.
 */
class QuestionCreationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that question creation persists data correctly.
     * Runs 100+ iterations with random data to verify the property holds.
     *
     * @test
     */
    public function property_question_creation_persistence(): void
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random valid question data
            $user = User::factory()->create();
            $questionData = [
                'user_id' => $user->id,
                'question' => $this->generateRandomString(10, 255),
                'description' => $this->generateRandomString(10, 1000),
                'tags' => $this->generateRandomTags(),
                'status' => $this->generateRandomStatus(),
            ];

            // Create the question
            $question = Questions::create($questionData);

            // Retrieve the question from database
            $retrievedQuestion = Questions::find($question->id);

            // Assert the question exists
            $this->assertNotNull($retrievedQuestion, "Question should exist in database (iteration {$i})");

            // Assert all fields match
            $this->assertEquals(
                $questionData['user_id'],
                $retrievedQuestion->user_id,
                "User ID should match (iteration {$i})"
            );
            
            $this->assertEquals(
                $questionData['question'],
                $retrievedQuestion->question,
                "Question text should match (iteration {$i})"
            );
            
            $this->assertEquals(
                $questionData['description'],
                $retrievedQuestion->description,
                "Description should match (iteration {$i})"
            );
            
            $this->assertEquals(
                $questionData['tags'],
                $retrievedQuestion->tags,
                "Tags should match (iteration {$i})"
            );
            
            $this->assertEquals(
                $questionData['status'],
                $retrievedQuestion->status,
                "Status should match (iteration {$i})"
            );

            // Clean up for next iteration
            $question->forceDelete();
            $user->forceDelete();
        }
    }

    /**
     * Generate a random string of specified length range.
     */
    private function generateRandomString(int $minLength, int $maxLength): string
    {
        $length = rand($minLength, min($maxLength, 500)); // Cap at 500 for performance
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 .,!?';
        $string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $string;
    }

    /**
     * Generate random tags.
     */
    private function generateRandomTags(): string
    {
        $tagCount = rand(1, 5);
        $tags = [];
        
        for ($i = 0; $i < $tagCount; $i++) {
            $tags[] = $this->generateRandomString(3, 15);
        }
        
        return implode(',', $tags);
    }

    /**
     * Generate random status.
     */
    private function generateRandomStatus(): string
    {
        $statuses = ['open', 'closed', 'pending', 'resolved'];
        return $statuses[array_rand($statuses)];
    }
}
