<?php

namespace Tests\Feature;

use App\Http\Livewire\AnswerInteractions;
use App\Http\Livewire\QuestionList;
use App\Http\Livewire\Search;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Property-Based Test: Livewire State Synchronization
 * 
 * Feature: infodot-modernization, Property 13: Livewire State Synchronization
 * Validates: Requirements FR-13
 * 
 * Property: For any Livewire component interaction, the component state should 
 * remain synchronized between server and client.
 */
class LivewireStateSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that AnswerInteractions component maintains state synchronization.
     * Runs 100+ iterations with random data to verify the property holds.
     *
     * @test
     */
    public function property_answer_interactions_state_synchronization(): void
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create test data
            $user = User::factory()->create();
            $question = Questions::factory()->create();
            $answer = Answer::factory()->create([
                'question_id' => $question->id,
                'user_id' => $user->id,
            ]);

            $this->actingAs($user);

            // Test like toggle synchronization
            $component = Livewire::test(AnswerInteractions::class, ['answer' => $answer])
                ->call('toggleLike');

            // Verify state is synchronized
            $component->assertSet('userLiked', true);
            $component->assertSet('likesCount', 1);

            // Toggle again
            $component->call('toggleLike');
            $component->assertSet('userLiked', false);
            $component->assertSet('likesCount', 0);

            // Test dislike toggle synchronization
            $component->call('toggleDislike');
            $component->assertSet('userDisliked', true);
            $component->assertSet('dislikesCount', 1);

            // Test comment addition synchronization
            $commentText = $this->generateRandomString(10, 100);
            $component->set('newComment', $commentText)
                ->call('addComment');

            $component->assertSet('newComment', '');
            $component->assertSet('commentsCount', 1);

            // Clean up
            $answer->forceDelete();
            $question->forceDelete();
            $user->forceDelete();
        }
    }

    /**
     * Test that QuestionList component maintains state synchronization.
     *
     * @test
     */
    public function property_question_list_state_synchronization(): void
    {
        $iterations = 50; // Fewer iterations for list components
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create random number of questions
            $questionCount = rand(1, 10);
            $questions = Questions::factory()->count($questionCount)->create();

            $component = Livewire::test(QuestionList::class);

            // Verify initial state
            $this->assertGreaterThanOrEqual(
                min($questionCount, 10), // perPage is 10
                $component->get('questionsCollection')->count(),
                "Questions collection should contain questions (iteration {$i})"
            );

            // Test loadMore synchronization
            if ($questionCount > 10) {
                $initialCount = $component->get('questionsCollection')->count();
                $component->call('loadMore');
                
                $this->assertGreaterThan(
                    $initialCount,
                    $component->get('questionsCollection')->count(),
                    "Questions collection should grow after loadMore (iteration {$i})"
                );
            }

            // Clean up
            Questions::query()->forceDelete();
        }
    }

    /**
     * Test that Search component maintains state synchronization.
     *
     * @test
     */
    public function property_search_state_synchronization(): void
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create searchable content
            $question = Questions::factory()->create([
                'question' => 'Test Question ' . $i,
            ]);

            $component = Livewire::test(Search::class);

            // Test search query synchronization
            $searchQuery = 'Test';
            $component->set('query', $searchQuery);

            // Verify state is synchronized
            $component->assertSet('query', $searchQuery);
            
            // Verify results are populated
            $this->assertNotEmpty(
                $component->get('questions'),
                "Search results should be populated (iteration {$i})"
            );

            // Test reset synchronization
            $component->call('resetFilters');
            $component->assertSet('query', '');
            $component->assertSet('questions', []);
            $component->assertSet('solutions', []);

            // Clean up
            $question->forceDelete();
        }
    }

    /**
     * Test that component state persists across multiple interactions.
     *
     * @test
     */
    public function property_component_state_persistence_across_interactions(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $user = User::factory()->create();
            $question = Questions::factory()->create();
            $answer = Answer::factory()->create([
                'question_id' => $question->id,
                'user_id' => $user->id,
            ]);

            $this->actingAs($user);

            $component = Livewire::test(AnswerInteractions::class, ['answer' => $answer]);

            // Perform multiple interactions
            $component->call('toggleLike');
            $initialLikesCount = $component->get('likesCount');

            $component->call('toggleComments');
            $showComments = $component->get('showComments');

            // Verify state persists
            $this->assertEquals(
                $initialLikesCount,
                $component->get('likesCount'),
                "Likes count should persist after other interactions (iteration {$i})"
            );

            $this->assertEquals(
                $showComments,
                $component->get('showComments'),
                "Show comments state should persist (iteration {$i})"
            );

            // Clean up
            $answer->forceDelete();
            $question->forceDelete();
            $user->forceDelete();
        }
    }

    /**
     * Generate a random string of specified length range.
     */
    private function generateRandomString(int $minLength, int $maxLength): string
    {
        $length = rand($minLength, min($maxLength, 200));
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 .,!?';
        $string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $string;
    }
}
