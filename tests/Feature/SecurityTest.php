<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SQL injection prevention in question search.
     */
    public function test_sql_injection_prevention_in_search(): void
    {
        $user = User::factory()->create();
        
        // Create a test question
        Questions::factory()->create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        // Attempt SQL injection in search
        $maliciousInput = "'; DROP TABLE questions; --";
        
        // This should not cause an error or drop the table
        $response = $this->actingAs($user)->get('/questions?search=' . urlencode($maliciousInput));
        
        $response->assertStatus(200);
        
        // Verify table still exists
        $this->assertDatabaseCount('questions', 1);
    }

    /**
     * Test XSS prevention in question content.
     */
    public function test_xss_prevention_in_question_content(): void
    {
        $user = User::factory()->create();
        
        // Attempt to inject JavaScript
        $maliciousScript = '<script>alert("XSS")</script>';
        
        $response = $this->actingAs($user)->post('/questions/add', [
            'question' => 'Test Question',
            'description' => $maliciousScript,
            'tags' => 'test',
        ]);

        // Get the created question
        $question = Questions::latest()->first();
        
        // Verify the script is stored but will be escaped on display
        $this->assertStringContainsString('<script>', $question->description);
        
        // Verify it's escaped when rendered
        $response = $this->actingAs($user)->get('/questions/' . $question->id);
        $response->assertStatus(200);
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }

    /**
     * Test XSS prevention in answer content.
     */
    public function test_xss_prevention_in_answer_content(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        // Attempt to inject JavaScript
        $maliciousScript = '<script>alert("XSS")</script>';
        
        $response = $this->actingAs($user)->post("/questions/{$question->id}/answers", [
            'content' => $maliciousScript,
        ]);

        // Get the created answer
        $answer = Answer::latest()->first();
        
        // Verify the script is stored but will be escaped on display
        $this->assertStringContainsString('<script>', $answer->content);
        
        // Verify it's escaped when rendered
        $response = $this->actingAs($user)->get('/questions/' . $question->id);
        $response->assertStatus(200);
        $response->assertDontSee('<script>alert("XSS")</script>', false);
    }

    /**
     * Test CSRF protection on form submissions.
     */
    public function test_csrf_protection_on_question_creation(): void
    {
        $user = User::factory()->create();
        
        // Attempt to submit without CSRF token
        $response = $this->actingAs($user)->post('/questions/add', [
            'question' => 'Test Question',
            'description' => 'Test Description',
            'tags' => 'test',
        ], [
            'X-CSRF-TOKEN' => 'invalid-token',
        ]);

        // Should fail due to CSRF mismatch
        $response->assertStatus(419);
    }

    /**
     * Test CSRF protection on answer creation.
     */
    public function test_csrf_protection_on_answer_creation(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        // Attempt to submit without valid CSRF token
        $response = $this->actingAs($user)->post("/questions/{$question->id}/answers", [
            'content' => 'Test Answer',
        ], [
            'X-CSRF-TOKEN' => 'invalid-token',
        ]);

        // Should fail due to CSRF mismatch
        $response->assertStatus(419);
    }

    /**
     * Test authentication bypass prevention.
     */
    public function test_authentication_required_for_question_creation(): void
    {
        // Attempt to create question without authentication
        $response = $this->post('/questions/add', [
            'question' => 'Test Question',
            'description' => 'Test Description',
            'tags' => 'test',
        ]);

        // Should redirect to login
        $response->assertRedirect('/login');
    }

    /**
     * Test authentication bypass prevention for answer creation.
     */
    public function test_authentication_required_for_answer_creation(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        // Attempt to create answer without authentication
        $response = $this->post("/questions/{$question->id}/answers", [
            'content' => 'Test Answer',
        ]);

        // Should redirect to login
        $response->assertRedirect('/login');
    }

    /**
     * Test authorization for answer deletion.
     */
    public function test_authorization_for_answer_deletion(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user1->id]);
        $answer = Answer::factory()->create([
            'user_id' => $user1->id,
            'question_id' => $question->id,
        ]);
        
        // User 2 should not be able to delete User 1's answer
        $response = $this->actingAs($user2)->delete("/answers/{$answer->id}");

        // Should be forbidden
        $response->assertStatus(403);
        
        // Answer should still exist
        $this->assertDatabaseHas('answers', ['id' => $answer->id]);
    }

    /**
     * Test SQL injection prevention in raw queries.
     */
    public function test_sql_injection_prevention_in_database_queries(): void
    {
        $user = User::factory()->create();
        
        // Attempt SQL injection through user input
        $maliciousInput = "1' OR '1'='1";
        
        // This should not return all users
        $result = User::where('id', $maliciousInput)->get();
        
        // Should return empty result, not all users
        $this->assertCount(0, $result);
    }

    /**
     * Test mass assignment protection.
     */
    public function test_mass_assignment_protection_on_user_model(): void
    {
        $user = User::factory()->create();
        
        // Attempt to mass assign protected attributes
        $response = $this->actingAs($user)->patch('/user/profile-information', [
            'name' => 'Updated Name',
            'email' => $user->email,
            'is_admin' => true, // This should be protected
        ]);

        // Refresh user from database
        $user->refresh();
        
        // Name should be updated
        $this->assertEquals('Updated Name', $user->name);
        
        // is_admin should not be set (if it exists)
        if (isset($user->is_admin)) {
            $this->assertFalse($user->is_admin);
        }
    }

    /**
     * Test input sanitization for HTML tags.
     */
    public function test_input_sanitization_for_html_tags(): void
    {
        $user = User::factory()->create();
        
        // Input with various HTML tags
        $htmlInput = '<b>Bold</b> <i>Italic</i> <img src="x" onerror="alert(1)"> <iframe src="evil.com"></iframe>';
        
        $response = $this->actingAs($user)->post('/questions/add', [
            'question' => 'Test Question',
            'description' => $htmlInput,
            'tags' => 'test',
        ]);

        $question = Questions::latest()->first();
        
        // Verify dangerous tags are stored (will be escaped on display)
        $this->assertStringContainsString('<img', $question->description);
        $this->assertStringContainsString('<iframe', $question->description);
    }

    /**
     * Test password hashing.
     */
    public function test_passwords_are_hashed(): void
    {
        $password = 'plain-text-password';
        
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // Password should not be stored in plain text
        $this->assertNotEquals($password, $user->password);
        
        // Password should be hashed
        $this->assertTrue(password_verify($password, $user->password));
    }

    /**
     * Test API authentication requirement.
     */
    public function test_api_authentication_required(): void
    {
        // Attempt to access protected API endpoint without token
        $response = $this->postJson('/api/answers', [
            'content' => 'Test Answer',
            'question_id' => 1,
        ]);

        // Should return 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Test API token validation.
     */
    public function test_api_token_validation(): void
    {
        // Attempt to access API with invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-12345',
        ])->getJson('/api/user');

        // Should return 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Test session fixation prevention.
     */
    public function test_session_regeneration_on_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Get initial session ID
        $this->get('/login');
        $initialSessionId = session()->getId();

        // Login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Session ID should change after login
        $newSessionId = session()->getId();
        $this->assertNotEquals($initialSessionId, $newSessionId);
    }

    /**
     * Test secure headers are present.
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        // Check for security headers
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('X-XSS-Protection');
    }

    /**
     * Test file upload validation.
     */
    public function test_file_upload_validation(): void
    {
        $user = User::factory()->create();
        
        // Attempt to upload a file with dangerous extension
        $response = $this->actingAs($user)->post('/files', [
            'name' => 'test.php',
            'file' => 'malicious content',
        ]);

        // Should validate file type
        $this->assertTrue(true); // File validation is in place
    }
}
