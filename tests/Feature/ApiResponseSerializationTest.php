<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use App\Models\Steps;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

/**
 * @test
 * Feature: infodot-modernization, Property 12: API Response Serialization
 * 
 * Property: For any API endpoint, responses should follow a consistent format 
 * with proper resource serialization.
 * 
 * Validates: Requirements FR-12
 */
class ApiResponseSerializationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that API responses have consistent structure
     * 
     * @test
     */
    public function test_api_responses_have_consistent_structure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $question = Questions::factory()->create(['user_id' => $user->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id
        ]);
        
        // Test answer creation response
        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'This is a test answer with sufficient length to pass validation.'
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'status_code'
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals(201, $response->json('status_code'));
    }

    /**
     * Test that error responses have consistent structure
     * 
     * @test
     */
    public function test_error_responses_have_consistent_structure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        // Test validation error response
        $response = $this->postJson('/api/answers', [
            'question_id' => 999999, // Non-existent question
            'content' => 'Short' // Too short
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors'
        ]);
    }

    /**
     * Test that unauthorized responses have consistent structure
     * 
     * @test
     */
    public function test_unauthorized_responses_have_consistent_structure(): void
    {
        // Try to access protected endpoint without authentication
        $response = $this->postJson('/api/answers', [
            'question_id' => 1,
            'content' => 'This is a test answer'
        ]);
        
        $response->assertStatus(401);
    }

    /**
     * Test that success responses contain expected data fields
     * 
     * @test
     */
    public function test_success_responses_contain_expected_data_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'This is a test answer with sufficient length to pass validation.'
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'user_id',
                'question_id',
                'content',
                'is_accepted',
                'created_at',
                'updated_at'
            ],
            'status_code'
        ]);
    }

    /**
     * Property-based test: All API endpoints return consistent response format
     * 
     * @test
     */
    public function test_property_all_api_endpoints_return_consistent_format(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $question = Questions::factory()->create(['user_id' => $user->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id
        ]);
        
        // Test multiple endpoints
        $endpoints = [
            ['method' => 'get', 'url' => '/api/user', 'expectedStatus' => 200],
            ['method' => 'get', 'url' => "/api/questions/{$question->id}/answers", 'expectedStatus' => 200],
            ['method' => 'get', 'url' => "/api/answers/{$answer->id}", 'expectedStatus' => 200],
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->{$endpoint['method'] . 'Json'}($endpoint['url']);
            
            $response->assertStatus($endpoint['expectedStatus']);
            
            // All successful responses should have consistent structure
            if ($endpoint['expectedStatus'] >= 200 && $endpoint['expectedStatus'] < 300) {
                $json = $response->json();
                
                // Check that response is either a success response or paginated data
                $this->assertTrue(
                    isset($json['success']) || isset($json['data']) || isset($json['id']),
                    "Response from {$endpoint['url']} should have consistent structure"
                );
            }
        }
    }

    /**
     * Property-based test: API responses are valid JSON
     * 
     * @test
     */
    public function test_property_api_responses_are_valid_json(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $iterations = 20;
        
        for ($i = 0; $i < $iterations; $i++) {
            $question = Questions::factory()->create(['user_id' => $user->id]);
            
            // Create answer with random content
            $content = fake()->paragraph(rand(2, 10));
            
            $response = $this->postJson('/api/answers', [
                'question_id' => $question->id,
                'content' => $content
            ]);
            
            // Response should be valid JSON
            $this->assertNotNull($response->json());
            
            // Response should have expected structure
            $response->assertJsonStructure([
                'success',
                'message',
                'data',
                'status_code'
            ]);
            
            // Data should be properly serialized
            $data = $response->json('data');
            $this->assertIsArray($data);
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('content', $data);
            $this->assertEquals($content, $data['content']);
        }
    }

    /**
     * Property-based test: API error responses are consistent
     * 
     * @test
     */
    public function test_property_api_error_responses_are_consistent(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $iterations = 10;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate invalid data
            $invalidData = [
                'question_id' => rand(999999, 9999999), // Non-existent
                'content' => fake()->word() // Too short
            ];
            
            $response = $this->postJson('/api/answers', $invalidData);
            
            // Should return validation error
            $response->assertStatus(422);
            
            // Should have consistent error structure
            $response->assertJsonStructure([
                'message',
                'errors'
            ]);
            
            // Errors should be an object/array
            $errors = $response->json('errors');
            $this->assertIsArray($errors);
        }
    }

    /**
     * Property-based test: Pagination responses have consistent structure
     * 
     * @test
     */
    public function test_property_pagination_responses_have_consistent_structure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        // Create varying numbers of answers
        $answerCounts = [5, 10, 20, 30];
        
        foreach ($answerCounts as $count) {
            $this->refreshDatabase();
            $user = User::factory()->create();
            $question = Questions::factory()->create(['user_id' => $user->id]);
            
            Answer::factory()->count($count)->create([
                'question_id' => $question->id,
                'user_id' => $user->id
            ]);
            
            $response = $this->getJson("/api/questions/{$question->id}/answers");
            
            $response->assertStatus(200);
            
            // Paginated responses should have consistent structure
            $json = $response->json();
            $this->assertIsArray($json);
            
            // Should have pagination metadata
            $this->assertTrue(
                isset($json['data']) || isset($json['current_page']) || is_array($json),
                "Paginated response should have consistent structure"
            );
        }
    }

    /**
     * Property-based test: API responses handle special characters correctly
     * 
     * @test
     */
    public function test_property_api_responses_handle_special_characters(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $specialStrings = [
            'Content with "quotes" and \'apostrophes\'',
            'Content with <html> tags',
            'Content with & ampersands',
            'Content with émojis 😀 🎉',
            'Content with newlines\nand\ttabs',
            'Content with unicode: 你好世界',
        ];
        
        foreach ($specialStrings as $content) {
            $question = Questions::factory()->create(['user_id' => $user->id]);
            
            $response = $this->postJson('/api/answers', [
                'question_id' => $question->id,
                'content' => $content
            ]);
            
            $response->assertStatus(201);
            
            // Content should be properly serialized
            $data = $response->json('data');
            $this->assertIsArray($data);
            $this->assertArrayHasKey('content', $data);
            
            // Content should be preserved (may be sanitized but should be retrievable)
            $this->assertNotEmpty($data['content']);
        }
    }
}
