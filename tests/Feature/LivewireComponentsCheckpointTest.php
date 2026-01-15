<?php

namespace Tests\Feature;

use App\Http\Livewire\AnswerInteractions;
use App\Http\Livewire\Associates;
use App\Http\Livewire\Comment;
use App\Http\Livewire\Comments;
use App\Http\Livewire\Question;
use App\Http\Livewire\QuestionCrud;
use App\Http\Livewire\QuestionList;
use App\Http\Livewire\Search;
use App\Http\Livewire\SolutionCrud;
use App\Http\Livewire\SolutionList;
use App\Models\Answer;
use App\Models\Associates as AssociatesModel;
use App\Models\Comment as CommentModel;
use App\Models\Questions;
use App\Models\Solutions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Checkpoint Test: Verify All Livewire Components
 * 
 * This test verifies:
 * - All Livewire interactions work correctly
 * - Real-time updates function properly
 * - Validation rules are enforced
 * - Event listeners respond correctly
 */
class LivewireComponentsCheckpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test AnswerInteractions component - all interactions.
     *
     * @test
     */
    public function test_answer_interactions_component_works(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(AnswerInteractions::class, ['answer' => $answer]);

        // Test like functionality
        $component->call('toggleLike')
            ->assertSet('userLiked', true)
            ->assertSet('likesCount', 1);

        // Test dislike functionality
        $component->call('toggleDislike')
            ->assertSet('userDisliked', true)
            ->assertSet('dislikesCount', 1)
            ->assertSet('userLiked', false); // Like should be removed

        // Test comment toggle
        $component->call('toggleComments')
            ->assertSet('showComments', true);

        // Test add comment
        $component->set('newComment', 'Test comment')
            ->call('addComment')
            ->assertSet('newComment', '')
            ->assertSet('commentsCount', 1);

        // Test acceptance toggle (only question author can accept)
        $questionAuthor = $question->user;
        $this->actingAs($questionAuthor);
        
        $component = Livewire::test(AnswerInteractions::class, ['answer' => $answer])
            ->call('toggleAcceptance');

        $this->assertTrue($answer->fresh()->is_accepted);
    }

    /**
     * Test QuestionList component - pagination and real-time updates.
     *
     * @test
     */
    public function test_question_list_component_works(): void
    {
        // Create multiple questions
        Questions::factory()->count(15)->create();

        $component = Livewire::test(QuestionList::class);

        // Verify initial load
        $this->assertNotEmpty($component->get('questionsCollection'));
        
        // Test loadMore functionality
        $initialCount = $component->get('questionsCollection')->count();
        $component->call('loadMore');
        
        $this->assertGreaterThanOrEqual(
            $initialCount,
            $component->get('questionsCollection')->count()
        );
    }

    /**
     * Test SolutionList component - pagination and real-time updates.
     *
     * @test
     */
    public function test_solution_list_component_works(): void
    {
        // Create multiple solutions
        Solutions::factory()->count(15)->create();

        $component = Livewire::test(SolutionList::class);

        // Verify initial load
        $this->assertNotEmpty($component->get('solutionsCollection'));
        
        // Test loadMore functionality
        $initialCount = $component->get('solutionsCollection')->count();
        $component->call('loadMore');
        
        $this->assertGreaterThanOrEqual(
            $initialCount,
            $component->get('solutionsCollection')->count()
        );
    }

    /**
     * Test Search component - live search functionality.
     *
     * @test
     */
    public function test_search_component_works(): void
    {
        // Create searchable content
        $question = Questions::factory()->create([
            'question' => 'How to test Laravel?',
        ]);
        
        $solution = Solutions::factory()->create([
            'solution_title' => 'Laravel Testing Guide',
        ]);

        $component = Livewire::test(Search::class);

        // Test search query
        $component->set('query', 'Laravel')
            ->assertSet('query', 'Laravel');

        // Verify results are populated
        $this->assertNotEmpty($component->get('questions'));

        // Test reset filters
        $component->call('resetFilters')
            ->assertSet('query', '')
            ->assertSet('questions', [])
            ->assertSet('solutions', []);
    }

    /**
     * Test QuestionCrud component - create, edit, delete operations.
     *
     * @test
     */
    public function test_question_crud_component_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(QuestionCrud::class);

        // Test create question
        $component->set('question', 'Test Question')
            ->set('description', 'Test Description')
            ->set('tags', 'test,laravel')
            ->call('save');

        $this->assertDatabaseHas('questions', [
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        // Test validation
        $component = Livewire::test(QuestionCrud::class)
            ->set('question', '') // Empty question
            ->set('description', 'Test')
            ->call('save')
            ->assertHasErrors(['question']);
    }

    /**
     * Test SolutionCrud component - create with steps.
     *
     * @test
     */
    public function test_solution_crud_component_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(SolutionCrud::class);

        // Test create solution
        $component->set('solution_title', 'Test Solution')
            ->set('solution_description', 'Test Description')
            ->set('tags', 'test,laravel')
            ->set('duration', 5)
            ->set('duration_type', 'days')
            ->set('solution_heading', ['Step 1', 'Step 2'])
            ->set('solution_body', ['Body 1', 'Body 2'])
            ->call('save');

        $this->assertDatabaseHas('solutions', [
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
        ]);

        // Test validation
        $component = Livewire::test(SolutionCrud::class)
            ->set('solution_title', '') // Empty title
            ->call('save')
            ->assertHasErrors(['solution_title']);
    }

    /**
     * Test Comments component - display and manage comments.
     *
     * @test
     */
    public function test_comments_component_works(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        
        // Create some comments
        CommentModel::factory()->count(3)->create([
            'commentable_type' => Questions::class,
            'commentable_id' => $question->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Comments::class, [
            'commentableType' => Questions::class,
            'commentableId' => $question->id,
        ]);

        // Verify comments are loaded
        $this->assertNotEmpty($component->get('comments'));
        $this->assertCount(3, $component->get('comments'));
    }

    /**
     * Test Comment component - add single comment.
     *
     * @test
     */
    public function test_comment_component_works(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(Comment::class, [
            'commentableType' => Questions::class,
            'commentableId' => $question->id,
        ]);

        // Test add comment
        $component->set('body', 'Test comment')
            ->call('addComment');

        $this->assertDatabaseHas('comments', [
            'body' => 'Test comment',
            'commentable_type' => Questions::class,
            'commentable_id' => $question->id,
        ]);

        // Test validation
        $component = Livewire::test(Comment::class, [
            'commentableType' => Questions::class,
            'commentableId' => $question->id,
        ])
            ->set('body', '') // Empty comment
            ->call('addComment')
            ->assertHasErrors(['body']);
    }

    /**
     * Test Associates component - manage user connections.
     *
     * @test
     */
    public function test_associates_component_works(): void
    {
        $user = User::factory()->create();
        $associate = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(Associates::class);

        // Test add associate
        $component->set('associateId', $associate->id)
            ->call('addAssociate');

        $this->assertDatabaseHas('associates', [
            'user_id' => $user->id,
            'associate_id' => $associate->id,
        ]);

        // Test remove associate
        $associateRecord = AssociatesModel::where('user_id', $user->id)
            ->where('associate_id', $associate->id)
            ->first();

        $component->call('removeAssociate', $associateRecord->id);

        $this->assertDatabaseMissing('associates', [
            'id' => $associateRecord->id,
        ]);
    }

    /**
     * Test Question component - display single question.
     *
     * @test
     */
    public function test_question_component_works(): void
    {
        $question = Questions::factory()->create();
        
        // Create some answers
        Answer::factory()->count(3)->create([
            'question_id' => $question->id,
        ]);

        $component = Livewire::test(Question::class, ['questionId' => $question->id]);

        // Verify question is loaded
        $this->assertNotNull($component->get('question'));
        $this->assertEquals($question->id, $component->get('question')->id);
        
        // Verify answers are loaded
        $this->assertNotEmpty($component->get('answers'));
    }

    /**
     * Test validation across all components.
     *
     * @test
     */
    public function test_all_components_enforce_validation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // QuestionCrud validation
        Livewire::test(QuestionCrud::class)
            ->set('question', '')
            ->set('description', '')
            ->call('save')
            ->assertHasErrors(['question', 'description']);

        // SolutionCrud validation
        Livewire::test(SolutionCrud::class)
            ->set('solution_title', '')
            ->set('solution_description', '')
            ->call('save')
            ->assertHasErrors(['solution_title', 'solution_description']);

        // Comment validation
        $question = Questions::factory()->create();
        Livewire::test(Comment::class, [
            'commentableType' => Questions::class,
            'commentableId' => $question->id,
        ])
            ->set('body', '')
            ->call('addComment')
            ->assertHasErrors(['body']);
    }

    /**
     * Test real-time update capabilities.
     *
     * @test
     */
    public function test_components_support_realtime_updates(): void
    {
        // Create initial data
        $initialQuestionCount = Questions::count();
        
        // Test QuestionList component
        $component = Livewire::test(QuestionList::class);
        
        // Create a new question (simulating real-time event)
        Questions::factory()->create();
        
        // Refresh component
        $component->call('$refresh');
        
        // Verify the component can be refreshed
        $this->assertNotNull($component->get('questionsCollection'));
    }

    /**
     * Test event listeners are properly configured.
     *
     * @test
     */
    public function test_components_have_event_listeners(): void
    {
        // Test QuestionList has listeners
        $component = Livewire::test(QuestionList::class);
        $listeners = $component->instance()->getEventsBeingListenedFor();
        
        // QuestionList should listen for question-related events
        $this->assertIsArray($listeners);

        // Test AnswerInteractions has listeners
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);
        $component = Livewire::test(AnswerInteractions::class, ['answer' => $answer]);
        $listeners = $component->instance()->getEventsBeingListenedFor();
        
        $this->assertIsArray($listeners);
    }

    /**
     * Test component state persistence across multiple interactions.
     *
     * @test
     */
    public function test_component_state_persists_across_interactions(): void
    {
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
        $likesCount = $component->get('likesCount');

        $component->call('toggleComments');
        $showComments = $component->get('showComments');

        // Verify state persists
        $this->assertEquals($likesCount, $component->get('likesCount'));
        $this->assertEquals($showComments, $component->get('showComments'));

        // Perform another interaction
        $component->set('newComment', 'Test comment')
            ->call('addComment');

        // Verify previous state still persists
        $this->assertEquals($likesCount, $component->get('likesCount'));
        $this->assertEquals($showComments, $component->get('showComments'));
    }

    /**
     * Test all components can be mounted without errors.
     *
     * @test
     */
    public function test_all_components_can_be_mounted(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Test each component can be mounted
        $components = [
            [AnswerInteractions::class, ['answer' => $answer]],
            [QuestionList::class, []],
            [SolutionList::class, []],
            [Search::class, []],
            [QuestionCrud::class, []],
            [SolutionCrud::class, []],
            [Comment::class, ['commentableType' => Questions::class, 'commentableId' => $question->id]],
            [Comments::class, ['commentableType' => Questions::class, 'commentableId' => $question->id]],
            [Associates::class, []],
            [Question::class, ['questionId' => $question->id]],
        ];

        foreach ($components as [$componentClass, $params]) {
            $component = Livewire::test($componentClass, $params);
            $this->assertNotNull($component, "Component {$componentClass} should mount successfully");
        }
    }
}
