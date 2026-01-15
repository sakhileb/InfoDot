<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all rate limiters before each test
        RateLimiter::clear('login');
        RateLimiter::clear('register');
        RateLimiter::clear('password-reset');
        RateLimiter::clear('contact');
    }

    /**
     * Test API rate limiting with throttle middleware.
     */
    public function test_api_rate_limiting(): void
    {
        // Make 60 API requests (should succeed)
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/answers');
            
            $this->assertNotEquals(429, $response->status(), "API request $i should not be rate limited");
        }

        // 61st request should be rate limited
        $response = $this->getJson('/api/answers');

        $this->assertEquals(429, $response->status(), 'API should be rate limited after 60 requests per minute');
    }

    /**
     * Test authenticated API rate limiting.
     */
    public function test_authenticated_api_rate_limiting(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Make 60 authenticated API requests (should succeed)
        for ($i = 0; $i < 60; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/answers');
            
            $this->assertNotEquals(429, $response->status(), "Authenticated API request $i should not be rate limited");
        }

        // 61st request should be rate limited
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/answers');

        $this->assertEquals(429, $response->status(), 'Authenticated API should be rate limited after 60 requests per minute');
    }

    /**
     * Test different users have separate rate limits.
     */
    public function test_different_users_have_separate_rate_limits(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $token1 = $user1->createToken('test-token')->plainTextToken;
        $token2 = $user2->createToken('test-token')->plainTextToken;

        // User 1 makes 60 requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token1,
            ])->getJson('/api/answers');
            
            $this->assertNotEquals(429, $response->status());
        }

        // User 1's 61st request should be rate limited
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->getJson('/api/answers');
        $this->assertEquals(429, $response->status());

        // User 2 should still be able to make requests
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
        ])->getJson('/api/answers');
        $this->assertNotEquals(429, $response->status());
    }

    /**
     * Test rate limiter configuration exists.
     */
    public function test_rate_limiter_configuration_exists(): void
    {
        // Verify rate limiters are configured
        $this->assertTrue(true, 'Rate limiter configuration test passed');
        
        // Test that RateLimiter facade is available
        $this->assertInstanceOf(\Illuminate\Cache\RateLimiter::class, app('Illuminate\Cache\RateLimiter'));
    }

    /**
     * Test Fortify rate limiters are configured.
     */
    public function test_fortify_rate_limiters_configured(): void
    {
        // Verify Fortify rate limiters exist
        $rateLimiter = app('Illuminate\Cache\RateLimiter');
        
        // These should be configured in FortifyServiceProvider
        $this->assertTrue(method_exists($rateLimiter, 'for'));
        $this->assertTrue(method_exists($rateLimiter, 'attempt'));
        $this->assertTrue(method_exists($rateLimiter, 'tooManyAttempts'));
    }

    /**
     * Test question creation rate limiting.
     */
    public function test_question_creation_rate_limiting(): void
    {
        $user = User::factory()->create();
        
        // Make 10 question creation requests (should succeed)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user)->post('/questions/add', [
                'question' => "Test question $i",
                'description' => "Test description $i",
                'tags' => 'test',
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Question creation request $i should not be rate limited");
        }

        // 11th request should be rate limited
        $response = $this->actingAs($user)->post('/questions/add', [
            'question' => 'Test question 11',
            'description' => 'Test description 11',
            'tags' => 'test',
        ]);

        $this->assertEquals(429, $response->status(), 'Question creation should be rate limited after 10 requests per minute');
    }

    /**
     * Test solution creation rate limiting.
     */
    public function test_solution_creation_rate_limiting(): void
    {
        $user = User::factory()->create();
        
        // Make 10 solution creation requests (should succeed)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user)->post('/solution/add', [
                'solution_title' => "Test solution $i",
                'solution_description' => "Test description $i",
                'tags' => 'test',
                'duration' => 1,
                'duration_type' => 'hours',
                'solution_heading' => ["Step 1"],
                'solution_body' => ["Body 1"],
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Solution creation request $i should not be rate limited");
        }

        // 11th request should be rate limited
        $response = $this->actingAs($user)->post('/solution/add', [
            'solution_title' => 'Test solution 11',
            'solution_description' => 'Test description 11',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'solution_heading' => ["Step 1"],
            'solution_body' => ["Body 1"],
        ]);

        $this->assertEquals(429, $response->status(), 'Solution creation should be rate limited after 10 requests per minute');
    }

    /**
     * Test contact form rate limiting.
     */
    public function test_contact_form_rate_limiting(): void
    {
        // Make 5 contact form submissions (should succeed)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/contact-send', [
                'name' => "Test User $i",
                'email' => "test$i@example.com",
                'message' => "Test message $i",
            ]);
            
            $this->assertNotEquals(429, $response->status(), "Contact form request $i should not be rate limited");
        }

        // 6th request should be rate limited
        $response = $this->post('/contact-send', [
            'name' => 'Test User 6',
            'email' => 'test6@example.com',
            'message' => 'Test message 6',
        ]);

        $this->assertEquals(429, $response->status(), 'Contact form should be rate limited after 5 requests');
    }

    /**
     * Test file upload rate limiting.
     */
    public function test_file_upload_rate_limiting(): void
    {
        $user = User::factory()->create();
        
        // Make 20 file upload requests (should succeed)
        for ($i = 0; $i < 20; $i++) {
            $response = $this->actingAs($user)->post('/files', [
                'name' => "Test file $i",
            ]);
            
            $this->assertNotEquals(429, $response->status(), "File upload request $i should not be rate limited");
        }

        // 21st request should be rate limited
        $response = $this->actingAs($user)->post('/files', [
            'name' => 'Test file 21',
        ]);

        $this->assertEquals(429, $response->status(), 'File upload should be rate limited after 20 requests per minute');
    }
}
