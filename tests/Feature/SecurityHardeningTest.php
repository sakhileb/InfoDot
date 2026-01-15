<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * Security Hardening Tests - Task 27.5
 * 
 * Comprehensive security testing covering:
 * - SQL injection prevention
 * - XSS prevention
 * - CSRF protection
 * - Authentication bypass attempts
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SQL injection prevention in question creation
     */
    public function test_sql_injection_prevention_in_questions(): void
    {
        $user = User::factory()->create();

        $sqlInjectionAttempts = [
            "'; DROP TABLE questions; --",
            "1' OR '1'='1",
            "admin'--",
            "' UNION SELECT * FROM users--",
            "1; DELETE FROM questions WHERE 1=1--",
        ];

        foreach ($sqlInjectionAttempts as $injection) {
            $response = $this->actingAs($user)
                ->withSession(['_token' => 'test-token'])
                ->post('/questions/add', [
                    '_token' => 'test-token',
                    'question' => $injection,
                    'description' => 'Test description',
                ]);

            // Verify the injection is safely stored as data, not executed
            $this->assertDatabaseHas('questions', [
                'question' => $injection,
            ]);
        }

        // Verify tables still exist
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('questions'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('users'));
    }

    /**
     * Test SQL injection prevention in answers
     */
    public function test_sql_injection_prevention_in_answers(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();

        $sqlInjectionAttempts = [
            "'; DROP TABLE answers; --",
            "1' OR '1'='1",
            "' UNION SELECT password FROM users--",
        ];

        foreach ($sqlInjectionAttempts as $injection) {
            $response = $this->actingAs($user)
                ->postJson('/api/answers', [
                    'question_id' => $question->id,
                    'content' => $injection,
                ]);

            $response->assertStatus(201);
            
            // Verify the injection is safely stored
            $this->assertDatabaseHas('answers', [
                'content' => $injection,
            ]);
        }

        // Verify tables still exist
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('answers'));
    }

    /**
     * Test SQL injection prevention in search queries
     */
    public function test_sql_injection_prevention_in_search(): void
    {
        $sqlInjectionAttempts = [
            "'; DROP TABLE questions; --",
            "1' OR '1'='1",
            "' UNION SELECT * FROM users--",
        ];

        foreach ($sqlInjectionAttempts as $injection) {
            // Search should safely handle SQL injection attempts
            $response = $this->get('/questions?search=' . urlencode($injection));

            // Should not cause an error
            $response->assertStatus(200);
        }

        // Verify tables still exist
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('questions'));
    }

    /**
     * Test XSS prevention in question content
     */
    public function test_xss_prevention_in_questions(): void
    {
        $user = User::factory()->create();

        $xssAttempts = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror="alert(\'XSS\')">',
            '<svg onload="alert(\'XSS\')">',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<body onload="alert(\'XSS\')">',
        ];

        foreach ($xssAttempts as $xss) {
            $response = $this->actingAs($user)
                ->withSession(['_token' => 'test-token'])
                ->post('/questions/add', [
                    '_token' => 'test-token',
                    'question' => 'Test Question',
                    'description' => $xss,
                ]);

            // Verify the XSS attempt is stored (will be escaped on output)
            $question = Questions::latest()->first();
            $this->assertStringContainsString($xss, $question->description);
        }
    }

    /**
     * Test XSS prevention in answer content
     */
    public function test_xss_prevention_in_answers(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();

        $xssAttempts = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror="alert(\'XSS\')">',
            '<svg onload="alert(\'XSS\')">',
        ];

        foreach ($xssAttempts as $xss) {
            $response = $this->actingAs($user)
                ->postJson('/api/answers', [
                    'question_id' => $question->id,
                    'content' => $xss,
                ]);

            $response->assertStatus(201);
            
            // Verify the XSS attempt is stored (will be escaped on output)
            $answer = Answer::latest()->first();
            $this->assertStringContainsString($xss, $answer->content);
        }
    }

    /**
     * Test XSS prevention in user profile
     */
    public function test_xss_prevention_in_user_profile(): void
    {
        $user = User::factory()->create([
            'name' => '<script>alert("XSS")</script>John Doe',
        ]);

        // Verify the XSS attempt is stored but will be escaped on output
        $this->assertStringContainsString('<script>', $user->name);
        
        // When rendered in Blade, it should be escaped
        $response = $this->actingAs($user)
            ->get('/user/profile/' . $user->id);

        $response->assertStatus(200);
        // The response should not contain executable script tags
        $response->assertDontSee('<script>alert("XSS")</script>', false);
    }

    /**
     * Test CSRF protection on question creation
     */
    public function test_csrf_protection_on_question_creation(): void
    {
        $user = User::factory()->create();

        // Attempt to create question without CSRF token
        $response = $this->actingAs($user)
            ->post('/questions/add', [
                'question' => 'Test question',
                'description' => 'Test description',
            ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /**
     * Test CSRF protection on answer creation (web route)
     */
    public function test_csrf_protection_on_answer_creation(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();

        // Attempt to create answer without CSRF token
        $response = $this->actingAs($user)
            ->post("/questions/{$question->id}/answers", [
                'content' => 'Test answer',
            ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /**
     * Test CSRF protection on contact form
     */
    public function test_csrf_protection_on_contact_form(): void
    {
        // Attempt to submit contact form without CSRF token
        $response = $this->post('/contact-send', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /**
     * Test authentication bypass attempt - direct API access
     */
    public function test_authentication_bypass_attempt_api(): void
    {
        $question = Questions::factory()->create();

        // Attempt to create answer without authentication
        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'Test answer',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('answers', [
            'content' => 'Test answer',
        ]);
    }

    /**
     * Test authentication bypass attempt - protected web routes
     */
    public function test_authentication_bypass_attempt_web(): void
    {
        // Attempt to access dashboard without authentication
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test authentication bypass attempt - question creation
     */
    public function test_authentication_bypass_attempt_question_creation(): void
    {
        // Attempt to access question creation page without authentication
        $response = $this->get('/questions/seek');

        $response->assertRedirect('/login');
    }

    /**
     * Test authentication bypass attempt - solution creation
     */
    public function test_authentication_bypass_attempt_solution_creation(): void
    {
        // Attempt to access solution creation page without authentication
        $response = $this->get('/solutions/create');

        $response->assertRedirect('/login');
    }

    /**
     * Test authorization bypass attempt - delete other user's answer
     */
    public function test_authorization_bypass_attempt_delete_answer(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create([
            'user_id' => $owner->id,
            'question_id' => $question->id,
        ]);

        // Attempt to delete another user's answer
        $response = $this->actingAs($attacker)
            ->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('answers', ['id' => $answer->id]);
    }

    /**
     * Test authorization bypass attempt - accept answer without question ownership
     */
    public function test_authorization_bypass_attempt_accept_answer(): void
    {
        $questionOwner = User::factory()->create();
        $answerOwner = User::factory()->create();
        $attacker = User::factory()->create();
        
        $question = Questions::factory()->create(['user_id' => $questionOwner->id]);
        $answer = Answer::factory()->create([
            'user_id' => $answerOwner->id,
            'question_id' => $question->id,
            'is_accepted' => false,
        ]);

        // Attempt to accept answer without question ownership
        $response = $this->actingAs($attacker)
            ->postJson("/api/answers/{$answer->id}/toggle-acceptance");

        $response->assertStatus(403);
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'is_accepted' => false,
        ]);
    }

    /**
     * Test password brute force protection
     */
    public function test_password_brute_force_protection(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        // Make multiple failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test session fixation prevention
     */
    public function test_session_fixation_prevention(): void
    {
        $user = User::factory()->create();

        // Get initial session ID
        $response = $this->get('/login');
        $initialSessionId = session()->getId();

        // Login
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Session ID should change after login
        $newSessionId = session()->getId();
        $this->assertNotEquals($initialSessionId, $newSessionId);
    }

    /**
     * Test secure password hashing
     */
    public function test_secure_password_hashing(): void
    {
        $password = 'test-password-123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        // Password should not be stored in plain text
        $this->assertNotEquals($password, $user->password);
        
        // Password should use bcrypt (starts with $2y$)
        $this->assertStringStartsWith('$2y$', $user->password);
        
        // Password should be verifiable
        $this->assertTrue(Hash::check($password, $user->password));
        
        // Wrong password should not verify
        $this->assertFalse(Hash::check('wrong-password', $user->password));
    }

    /**
     * Test API token security
     */
    public function test_api_token_security(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Valid token should work
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');
        $response->assertStatus(200);

        // Invalid token should be rejected
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/user');
        $response->assertStatus(401);

        // Missing token should be rejected
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    /**
     * Test mass assignment protection
     */
    public function test_mass_assignment_protection(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();

        // Attempt to set protected fields via mass assignment
        $response = $this->actingAs($user)
            ->postJson('/api/answers', [
                'question_id' => $question->id,
                'content' => 'Test answer',
                'is_accepted' => true, // Should not be mass assignable
                'user_id' => 999, // Should not be mass assignable
            ]);

        $response->assertStatus(201);
        
        $answer = Answer::latest()->first();
        
        // Verify protected fields were not set
        $this->assertFalse($answer->is_accepted);
        $this->assertEquals($user->id, $answer->user_id);
        $this->assertNotEquals(999, $answer->user_id);
    }

    /**
     * Test input validation prevents malformed data
     */
    public function test_input_validation_prevents_malformed_data(): void
    {
        $user = User::factory()->create();

        // Attempt to create answer with missing required fields
        $response = $this->actingAs($user)
            ->postJson('/api/answers', [
                'content' => '', // Empty content
            ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['question_id', 'content']);
    }

    /**
     * Test security headers are present
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        // Check for security headers
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('X-XSS-Protection');
        $response->assertHeader('Referrer-Policy');
    }

    /**
     * Test HTTPS redirect in production
     */
    public function test_https_redirect_configuration_exists(): void
    {
        // Verify ForceHttps middleware exists
        $this->assertTrue(
            class_exists(\App\Http\Middleware\ForceHttps::class),
            'ForceHttps middleware should exist'
        );
    }

    /**
     * Test rate limiting is enforced
     */
    public function test_rate_limiting_is_enforced(): void
    {
        // Make 60 API requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/answers');
            $this->assertNotEquals(429, $response->status());
        }

        // 61st request should be rate limited
        $response = $this->getJson('/api/answers');
        $this->assertEquals(429, $response->status());
    }
}
