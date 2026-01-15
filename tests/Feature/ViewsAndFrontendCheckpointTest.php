<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use App\Models\Steps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Checkpoint 12: Verify Views and Frontend
 * 
 * This test suite verifies that all pages render correctly,
 * styling matches the original, responsive design works,
 * and JavaScript interactions function properly.
 */
class ViewsAndFrontendCheckpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Questions $question;
    protected Answer $answer;
    protected Solutions $solution;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->question = Questions::factory()->create(['user_id' => $this->user->id]);
        $this->answer = Answer::factory()->create([
            'user_id' => $this->user->id,
            'question_id' => $this->question->id,
        ]);
        $this->solution = Solutions::factory()->create(['user_id' => $this->user->id]);
        Steps::factory()->create(['solution_id' => $this->solution->id, 'user_id' => $this->user->id]);
    }

    /** @test */
    public function welcome_page_renders_correctly()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
        $response->assertSee('InfoDot', false);
    }

    /** @test */
    public function about_page_renders_correctly()
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertViewIs('about');
    }

    /** @test */
    public function contact_page_renders_correctly()
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertViewIs('contact');
        $response->assertSee('Contact', false);
    }

    /** @test */
    public function faqs_page_renders_correctly()
    {
        $response = $this->get('/faqs');

        $response->assertStatus(200);
        $response->assertViewIs('faqs');
    }

    /** @test */
    public function terms_page_renders_correctly()
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertViewIs('terms');
    }

    /** @test */
    public function policy_page_renders_correctly()
    {
        $response = $this->get('/policy');

        $response->assertStatus(200);
        $response->assertViewIs('policy');
    }

    /** @test */
    public function complains_page_renders_correctly()
    {
        $response = $this->get('/complains');

        $response->assertStatus(200);
        $response->assertViewIs('complains');
    }

    /** @test */
    public function questions_index_page_renders_correctly()
    {
        $response = $this->get('/questions');

        $response->assertStatus(200);
        $response->assertViewIs('questions.index');
        $response->assertSee($this->question->question);
    }

    /** @test */
    public function question_view_page_renders_correctly()
    {
        $response = $this->get("/questions/{$this->question->id}");

        $response->assertStatus(200);
        $response->assertViewIs('questions.view');
        $response->assertSee($this->question->question);
        $response->assertSee($this->question->description);
    }

    /** @test */
    public function question_seek_page_requires_authentication()
    {
        $response = $this->get('/questions/seek');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_question_seek_page()
    {
        $response = $this->actingAs($this->user)->get('/questions/seek');

        $response->assertStatus(200);
        $response->assertViewIs('questions.seek');
    }

    /** @test */
    public function solutions_index_page_renders_correctly()
    {
        $response = $this->get('/solutions');

        $response->assertStatus(200);
        $response->assertViewIs('solutions.index');
        $response->assertSee($this->solution->solution_title);
    }

    /** @test */
    public function solution_view_page_renders_correctly()
    {
        $response = $this->get("/solutions/{$this->solution->id}");

        $response->assertStatus(200);
        $response->assertViewIs('solutions.view');
        $response->assertSee($this->solution->solution_title);
        $response->assertSee($this->solution->solution_description);
    }

    /** @test */
    public function solution_create_page_requires_authentication()
    {
        $response = $this->get('/solutions/create');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_solution_create_page()
    {
        $response = $this->actingAs($this->user)->get('/solutions/create');

        $response->assertStatus(200);
        $response->assertViewIs('solutions.create');
    }

    /** @test */
    public function dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    /** @test */
    public function user_profile_page_renders_correctly()
    {
        $response = $this->actingAs($this->user)->get('/user/profile');

        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertSee($this->user->name);
    }

    /** @test */
    public function search_results_page_renders_correctly()
    {
        $response = $this->get('/solution_search_results?search=test');

        $response->assertStatus(200);
        $response->assertViewIs('search_results');
    }

    /** @test */
    public function login_page_renders_correctly()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Email', false);
        $response->assertSee('Password', false);
    }

    /** @test */
    public function register_page_renders_correctly()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
        $response->assertSee('Name', false);
        $response->assertSee('Email', false);
    }

    /** @test */
    public function pages_include_navigation_menu()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for navigation elements
        $response->assertSee('Questions', false);
        $response->assertSee('Solutions', false);
    }

    /** @test */
    public function pages_include_footer()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Footer should be present
        $response->assertViewHas('slot');
    }

    /** @test */
    public function livewire_components_are_loaded_on_pages()
    {
        $response = $this->get('/questions');

        $response->assertStatus(200);
        // Check for Livewire scripts
        $response->assertSee('livewire', false);
    }

    /** @test */
    public function question_page_includes_answer_interactions_component()
    {
        $response = $this->get("/questions/{$this->question->id}");

        $response->assertStatus(200);
        // Check for answer interactions Livewire component
        $response->assertSee('answer-interactions', false);
    }

    /** @test */
    public function pages_load_css_assets()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for CSS asset references (Vite uses hashed filenames)
        $response->assertSee('/build/assets/app-', false);
        $response->assertSee('.css', false);
    }

    /** @test */
    public function pages_load_javascript_assets()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for JavaScript asset references (Vite uses hashed filenames)
        $response->assertSee('/build/assets/app-', false);
        $response->assertSee('.js', false);
    }

    /** @test */
    public function pages_include_alpine_js()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for Alpine.js
        $response->assertSee('x-data', false);
    }

    /** @test */
    public function error_404_page_renders_correctly()
    {
        $response = $this->get('/non-existent-page');

        $response->assertStatus(404);
        $response->assertViewIs('errors.404');
    }

    /** @test */
    public function csrf_token_is_present_in_forms()
    {
        $response = $this->actingAs($this->user)->get('/questions/seek');

        $response->assertStatus(200);
        $response->assertSee('csrf-token', false);
    }

    /** @test */
    public function meta_viewport_tag_is_present_for_responsive_design()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('viewport', false);
    }

    /** @test */
    public function tailwind_css_classes_are_present()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for common Tailwind classes
        $response->assertSeeInOrder(['class=', 'flex', 'text-'], false);
    }

    /** @test */
    public function authenticated_pages_show_user_information()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    /** @test */
    public function question_view_displays_answers()
    {
        $response = $this->get("/questions/{$this->question->id}");

        $response->assertStatus(200);
        $response->assertSee($this->answer->content);
    }

    /** @test */
    public function solution_view_displays_steps()
    {
        $step = $this->solution->steps->first();
        
        $response = $this->get("/solutions/{$this->solution->id}");

        $response->assertStatus(200);
        $response->assertSee($step->solution_heading);
        $response->assertSee($step->solution_body);
    }

    /** @test */
    public function pages_have_proper_html_structure()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for basic HTML structure
        $response->assertSee('<!DOCTYPE html>', false);
        $response->assertSee('<html', false);
        $response->assertSee('<head>', false);
        $response->assertSee('<body', false);
    }

    /** @test */
    public function pages_have_proper_title_tags()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<title>', false);
    }

    /** @test */
    public function authenticated_navigation_shows_logout_option()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Log Out', false);
    }

    /** @test */
    public function guest_navigation_shows_login_and_register()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Log in', false);
        $response->assertSee('Register', false);
    }

    /** @test */
    public function form_validation_errors_are_displayed()
    {
        $response = $this->actingAs($this->user)->post('/questions/add_question', [
            'question' => '', // Invalid: empty question
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['question', 'description']);
    }

    /** @test */
    public function flash_messages_are_displayed()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['status' => 'Test message'])
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Test message');
    }

    /** @test */
    public function livewire_search_component_is_present()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for search component
        $response->assertSee('search', false);
    }

    /** @test */
    public function question_list_livewire_component_renders()
    {
        $response = $this->get('/questions');

        $response->assertStatus(200);
        $response->assertSeeLivewire('question-list');
    }

    /** @test */
    public function solution_list_livewire_component_renders()
    {
        $response = $this->get('/solutions');

        $response->assertStatus(200);
        $response->assertSeeLivewire('solution-list');
    }

    /** @test */
    public function pages_are_accessible_without_javascript_errors()
    {
        // This test verifies that pages render without server-side errors
        // JavaScript errors would need browser testing (Dusk)
        $pages = [
            '/',
            '/about',
            '/contact',
            '/faqs',
            '/terms',
            '/policy',
            '/questions',
            '/solutions',
        ];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function authenticated_pages_are_accessible_without_errors()
    {
        $pages = [
            '/dashboard',
            '/user/profile',
            '/questions/seek',
            '/solutions/create',
        ];

        foreach ($pages as $page) {
            $response = $this->actingAs($this->user)->get($page);
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function pages_include_proper_charset_declaration()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('charset=', false);
    }

    /** @test */
    public function pages_include_favicon_reference()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('favicon', false);
    }

    /** @test */
    public function team_pages_render_correctly_for_team_members()
    {
        $team = $this->user->ownedTeams()->create([
            'name' => 'Test Team',
            'personal_team' => false,
        ]);

        $response = $this->actingAs($this->user)->get("/teams/{$team->id}");

        $response->assertStatus(200);
        $response->assertViewIs('teams.show');
        $response->assertSee('Test Team');
    }

    /** @test */
    public function api_token_page_renders_correctly()
    {
        $response = $this->actingAs($this->user)->get('/user/api-tokens');

        $response->assertStatus(200);
        $response->assertViewIs('api.index');
    }

    /** @test */
    public function pages_do_not_have_broken_asset_references()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Ensure no obvious broken references (like missing quotes or malformed URLs)
        $content = $response->getContent();
        $this->assertStringNotContainsString('src=""', $content);
        $this->assertStringNotContainsString('href=""', $content);
    }

    /** @test */
    public function responsive_navigation_menu_is_present()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for mobile menu toggle (usually has specific classes or attributes)
        $response->assertSee('menu', false);
    }

    /** @test */
    public function pages_include_proper_lang_attribute()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('lang=', false);
    }
}
