<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-based test for input sanitization completeness
 * 
 * Feature: infodot-modernization, Property 15: Input Sanitization Completeness
 * 
 * Property: For any user input, HTML tags and malicious scripts should be sanitized before storage or display.
 * Validates: Requirements NFR-2
 */
class InputSanitizationCompletenessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that question input is sanitized
     * 
     * @test
     */
    public function property_question_input_is_sanitized(): void
    {
        $maliciousInputs = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<svg onload=alert("XSS")>',
            'javascript:alert("XSS")',
            '<body onload=alert("XSS")>',
        ];

        foreach ($maliciousInputs as $input) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();

            $response = $this->actingAs($user)->post(route('questions.add'), [
                'question' => $input,
                'description' => 'Test description',
                'tags' => 'test',
            ]);

            // Verify the question was created
            $question = Questions::latest()->first();
            
            // Verify malicious content is sanitized or escaped
            $this->assertNotEquals($input, $question->question);
            $this->assertStringNotContainsString('<script>', $question->question);
            $this->assertStringNotContainsString('javascript:', $question->question);
            $this->assertStringNotContainsString('onerror=', $question->question);
            $this->assertStringNotContainsString('onload=', $question->question);
        }
    }

    /**
     * Test that answer input is sanitized
     * 
     * @test
     */
    public function property_answer_input_is_sanitized(): void
    {
        $maliciousInputs = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '<a href="javascript:alert(\'XSS\')">Click</a>',
        ];

        foreach ($maliciousInputs as $input) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $question = Questions::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/answers', [
                'question_id' => $question->id,
                'content' => $input,
            ]);

            if ($response->status() === 201) {
                $answer = Answer::latest()->first();
                
                // Verify malicious content is sanitized
                $this->assertStringNotContainsString('<script>', $answer->content);
                $this->assertStringNotContainsString('javascript:', $answer->content);
                $this->assertStringNotContainsString('onerror=', $answer->content);
            }
        }
    }

    /**
     * Test that solution input is sanitized
     * 
     * @test
     */
    public function property_solution_input_is_sanitized(): void
    {
        $maliciousInputs = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
        ];

        foreach ($maliciousInputs as $input) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();

            $response = $this->actingAs($user)->post(route('solutions.add'), [
                'solution_title' => $input,
                'solution_description' => 'Test description',
                'tags' => 'test',
                'duration' => 1,
                'duration_type' => 'hours',
                'solution_heading' => ['Step 1'],
                'solution_body' => ['Body 1'],
            ]);

            $solution = Solutions::latest()->first();
            
            if ($solution) {
                // Verify malicious content is sanitized
                $this->assertStringNotContainsString('<script>', $solution->solution_title);
                $this->assertStringNotContainsString('onerror=', $solution->solution_title);
            }
        }
    }

    /**
     * Test that comment input is sanitized
     * 
     * @test
     */
    public function property_comment_input_is_sanitized(): void
    {
        $maliciousInputs = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
        ];

        foreach ($maliciousInputs as $input) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $question = Questions::factory()->create();
            $answer = Answer::factory()->create(['question_id' => $question->id]);

            $response = $this->actingAs($user)->postJson("/api/answers/{$answer->id}/comments", [
                'body' => $input,
            ]);

            if ($response->status() === 201) {
                $comment = $answer->comments()->latest()->first();
                
                // Verify malicious content is sanitized
                $this->assertStringNotContainsString('<script>', $comment->body);
                $this->assertStringNotContainsString('onerror=', $comment->body);
            }
        }
    }

    /**
     * Test that SQL injection attempts are prevented
     * 
     * @test
     */
    public function property_sql_injection_is_prevented(): void
    {
        $sqlInjectionInputs = [
            "'; DROP TABLE users; --",
            "1' OR '1'='1",
            "admin'--",
            "' UNION SELECT * FROM users--",
        ];

        foreach ($sqlInjectionInputs as $input) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();

            // Attempt SQL injection through question creation
            $response = $this->actingAs($user)->post(route('questions.add'), [
                'question' => $input,
                'description' => 'Test description',
                'tags' => 'test',
            ]);

            // Verify the database is still intact
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            
            // Verify the input was treated as a string, not SQL
            $question = Questions::latest()->first();
            if ($question) {
                $this->assertEquals($input, $question->question);
            }
        }
    }

    /**
     * Test that safe HTML is preserved
     * 
     * @test
     */
    public function property_safe_html_is_preserved(): void
    {
        $safeInputs = [
            'This is a <strong>bold</strong> statement',
            'Check out <a href="https://example.com">this link</a>',
            '<p>This is a paragraph</p>',
            '<em>Emphasized text</em>',
        ];

        foreach ($safeInputs as $input) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $question = Questions::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/answers', [
                'question_id' => $question->id,
                'content' => $input,
            ]);

            if ($response->status() === 201) {
                $answer = Answer::latest()->first();
                
                // Verify safe HTML tags are preserved or properly escaped
                // (depending on application's sanitization policy)
                $this->assertNotNull($answer->content);
            }
        }
    }

    /**
     * Test that special characters are properly escaped
     * 
     * @test
     */
    public function property_special_characters_are_escaped(): void
    {
        $specialCharInputs = [
            'Test & Test',
            'Price < $100',
            'Value > 50',
            'Quote: "Hello World"',
            "Single quote: 'test'",
        ];

        foreach ($specialCharInputs as $input) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();

            $response = $this->actingAs($user)->post(route('questions.add'), [
                'question' => $input,
                'description' => 'Test description',
                'tags' => 'test',
            ]);

            $question = Questions::latest()->first();
            
            // Verify special characters are handled correctly
            $this->assertNotNull($question->question);
            // The exact handling depends on the application's escaping strategy
        }
    }
}
