<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Questions;
use App\Events\Questions\QuestionWasAsked;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Feature tests for question management functionality
 * 
 * Tests question creation, viewing, listing, and search
 * Requirements: FR-2, TR-1
 */
class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can view the question creation form
     */
    public function test_authenticated_users_can_view_question_creation_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('questions.seek'));

        $response->assertStatus(200);
        $response->assertViewIs('questions.seek');
    }

    /**
     * Test that guests cannot view the question creation form
     */
    public function test_guests_cannot_view_question_creation_form(): void
    {
        $response = $this->get(route('questions.seek'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that authenticated users can create a question
     */
    public function test_authenticated_users_can_create_question(): void
    {
        Event::fake();
        
        $user = User::factory()->create();

        $questionData = [
            'question' => 'How do I test Laravel applications?',
            'description' => 'I need help understanding how to write tests for my Laravel application.',
            'tags' => 'laravel,testing,phpunit',
        ];

        $response = $this->actingAs($user)->post(route('questions.add'), $questionData);

        $response->assertRedirect(route('questions.index'));
        $response->assertSessionHas('success', 'Question posted successfully!');

        $this->assertDatabaseHas('questions', [
            'user_id' => $user->id,
            'question' => $questionData['question'],
            'description' => $questionData['description'],
            'tags' => $questionData['tags'],
        ]);

        // Verify event was dispatched
        Event::assertDispatched(QuestionWasAsked::class);
    }

    /**
     * Test that question creation requires authentication
     */
    public function test_question_creation_requires_authentication(): void
    {
        $questionData = [
            'question' => 'Test question',
            'description' => 'Test description',
            'tags' => 'test',
        ];

        $response = $this->post(route('questions.add'), $questionData);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('questions', 0);
    }

    /**
     * Test that question creation validates required fields
     */
    public function test_question_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        // Test missing question
        $response = $this->actingAs($user)->post(route('questions.add'), [
            'description' => 'Test description',
            'tags' => 'test',
        ]);
        $response->assertSessionHasErrors('question');

        // Test missing description
        $response = $this->actingAs($user)->post(route('questions.add'), [
            'question' => 'Test question',
            'tags' => 'test',
        ]);
        $response->assertSessionHasErrors('description');
    }

    /**
     * Test that question creation validates minimum length
     */
    public function test_question_creation_validates_minimum_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('questions.add'), [
            'question' => 'ab', // Too short (min 3)
            'description' => 'ab', // Too short (min 3)
            'tags' => 'test',
        ]);

        $response->assertSessionHasErrors(['question', 'description']);
    }

    /**
     * Test that users can view a single question
     */
    public function test_users_can_view_single_question(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('questions.view', $question->id));

        $response->assertStatus(200);
        $response->assertViewIs('questions.view');
        $response->assertViewHas('question', function ($viewQuestion) use ($question) {
            return $viewQuestion->id === $question->id;
        });
    }

    /**
     * Test that viewing a non-existent question returns 404
     */
    public function test_viewing_nonexistent_question_returns_404(): void
    {
        $response = $this->get(route('questions.view', 99999));

        $response->assertStatus(404);
    }

    /**
     * Test that users can view the questions listing page
     */
    public function test_users_can_view_questions_listing(): void
    {
        $response = $this->get(route('questions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('questions.index');
    }

    /**
     * Test that questions are searchable
     */
    public function test_questions_are_searchable(): void
    {
        $user = User::factory()->create();
        
        // Create questions with specific content
        $question1 = Questions::factory()->create([
            'user_id' => $user->id,
            'question' => 'How to use Laravel Eloquent?',
            'description' => 'I need help with Eloquent ORM',
        ]);

        $question2 = Questions::factory()->create([
            'user_id' => $user->id,
            'question' => 'Vue.js integration with Laravel',
            'description' => 'How do I integrate Vue.js?',
        ]);

        // Search for Laravel-related questions
        $results = Questions::search('Laravel')->get();

        $this->assertTrue($results->contains($question1));
        $this->assertTrue($results->contains($question2));
    }

    /**
     * Test that questions can be filtered by tags
     */
    public function test_questions_can_be_filtered_by_tags(): void
    {
        $user = User::factory()->create();
        
        $laravelQuestion = Questions::factory()->create([
            'user_id' => $user->id,
            'tags' => 'laravel,php,backend',
        ]);

        $vueQuestion = Questions::factory()->create([
            'user_id' => $user->id,
            'tags' => 'vue,javascript,frontend',
        ]);

        // Filter by Laravel tag
        $laravelQuestions = Questions::where('tags', 'like', '%laravel%')->get();

        $this->assertTrue($laravelQuestions->contains($laravelQuestion));
        $this->assertFalse($laravelQuestions->contains($vueQuestion));
    }

    /**
     * Test that question view includes eager-loaded relationships
     */
    public function test_question_view_eager_loads_relationships(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create some answers for the question
        $question->answers()->create([
            'user_id' => $user->id,
            'content' => 'Test answer',
        ]);

        $response = $this->get(route('questions.view', $question->id));

        $response->assertStatus(200);
        
        // Verify the question has relationships loaded
        $viewQuestion = $response->viewData('question');
        $this->assertTrue($viewQuestion->relationLoaded('user'));
        $this->assertTrue($viewQuestion->relationLoaded('answers'));
    }

    /**
     * Test that question creation event is dispatched
     */
    public function test_question_creation_dispatches_event(): void
    {
        Event::fake();
        
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('questions.add'), [
            'question' => 'Test question',
            'description' => 'Test description',
            'tags' => 'test',
        ]);

        Event::assertDispatched(QuestionWasAsked::class, function ($event) use ($user) {
            return $event->question->user_id === $user->id;
        });
    }
}
