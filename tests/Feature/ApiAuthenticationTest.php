<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Answer;
use App\Models\Questions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Sanctum token generation
     */
    public function test_sanctum_token_generation(): void
    {
        $user = User::factory()->create();

        // Generate token
        $token = $user->createToken('test-token')->plainTextToken;

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        
        // Verify token exists in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
    }

    /**
     * Test authenticated endpoint access with valid token
     */
    public function test_authenticated_endpoint_with_valid_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test unauthenticated access returns 401
     */
    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test creating answer without authentication returns 401
     */
    public function test_creating_answer_without_authentication_returns_401(): void
    {
        $question = Questions::factory()->create();

        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'This is a test answer',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test creating answer with authentication succeeds
     */
    public function test_creating_answer_with_authentication_succeeds(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'This is a test answer',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'content',
                    'is_accepted',
                    'created_at',
                    'user',
                ],
            ]);
    }

    /**
     * Test unauthorized access to update answer returns 403
     */
    public function test_unauthorized_update_answer_returns_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/answers/{$answer->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    /**
     * Test authorized access to update answer succeeds
     */
    public function test_authorized_update_answer_succeeds(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/answers/{$answer->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Answer updated successfully',
            ]);
    }

    /**
     * Test unauthorized access to delete answer returns 403
     */
    public function test_unauthorized_delete_answer_returns_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    /**
     * Test authorized access to delete answer succeeds
     */
    public function test_authorized_delete_answer_succeeds(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Answer deleted successfully',
            ]);

        $this->assertDatabaseMissing('answers', [
            'id' => $answer->id,
        ]);
    }

    /**
     * Test token revocation
     */
    public function test_token_revocation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        // Token should work initially
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/user')
            ->assertStatus(200);

        // Revoke token
        $token->accessToken->delete();

        // Token should no longer work
        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->getJson('/api/user')
            ->assertStatus(401);
    }

    /**
     * Test multiple tokens for same user
     */
    public function test_multiple_tokens_for_same_user(): void
    {
        $user = User::factory()->create();
        
        $token1 = $user->createToken('device-1')->plainTextToken;
        $token2 = $user->createToken('device-2')->plainTextToken;

        // Both tokens should work
        $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->getJson('/api/user')
            ->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/user')
            ->assertStatus(200);

        // Verify both tokens exist
        $this->assertEquals(2, $user->tokens()->count());
    }

    /**
     * Test token abilities/scopes
     */
    public function test_token_abilities(): void
    {
        $user = User::factory()->create();
        
        // Create token with specific abilities
        $token = $user->createToken('test-token', ['answer:create', 'answer:read']);

        $this->assertContains('answer:create', $token->accessToken->abilities);
        $this->assertContains('answer:read', $token->accessToken->abilities);
        $this->assertNotContains('answer:delete', $token->accessToken->abilities);
    }

    /**
     * Test accessing protected route without token
     */
    public function test_accessing_protected_route_without_token(): void
    {
        $answer = Answer::factory()->create();

        $response = $this->postJson("/api/answers/{$answer->id}/like", [
            'like' => true,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test accessing protected route with token
     */
    public function test_accessing_protected_route_with_token(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/answers/{$answer->id}/like", [
            'like' => true,
        ]);

        $response->assertStatus(200);
    }
}
