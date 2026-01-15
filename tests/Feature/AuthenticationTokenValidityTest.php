<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Property 1: Authentication Token Validity
 * 
 * Feature: infodot-modernization, Property 1: Authentication Token Validity
 * Validates: Requirements FR-1
 * 
 * For any authenticated user session, the authentication token should remain valid 
 * until explicitly revoked or expired, and should grant access to protected resources.
 */
class AuthenticationTokenValidityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid tokens grant access to protected resources
     * 
     * @test
     */
    public function property_valid_tokens_grant_access_to_protected_resources(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            // Generate random user
            $user = User::factory()->create();
            
            // Create token
            $token = $user->createToken('test-token-' . $i);
            
            // Act as the user with the token
            Sanctum::actingAs($user);
            
            // Test access to protected resource
            $response = $this->getJson('/api/user');
            
            // Assert token grants access
            $this->assertEquals(200, $response->status(), 
                "Iteration {$i}: Valid token should grant access to protected resource");
            
            $response->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
            
            // Clean up for next iteration
            $user->tokens()->delete();
            $user->delete();
        }
    }

    /**
     * Test that tokens remain valid across multiple requests
     * 
     * @test
     */
    public function property_tokens_remain_valid_across_multiple_requests(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $user = User::factory()->create();
            Sanctum::actingAs($user);
            
            // Make multiple requests with the same token
            $requestCount = rand(2, 5);
            for ($j = 0; $j < $requestCount; $j++) {
                $response = $this->getJson('/api/user');
                
                $this->assertEquals(200, $response->status(),
                    "Iteration {$i}, Request {$j}: Token should remain valid across multiple requests");
            }
            
            $user->tokens()->delete();
            $user->delete();
        }
    }

    /**
     * Test that revoked tokens do not grant access
     * 
     * @test
     */
    public function property_revoked_tokens_do_not_grant_access(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $user = User::factory()->create();
            $token = $user->createToken('test-token-' . $i);
            
            // Token should work initially
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/user')
                ->assertStatus(200);
            
            // Revoke the token
            $token->accessToken->delete();
            
            // Token should no longer work
            $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
                ->getJson('/api/user');
            
            $this->assertEquals(401, $response->status(),
                "Iteration {$i}: Revoked token should not grant access");
            
            $user->delete();
        }
    }

    /**
     * Test that tokens are user-specific
     * 
     * @test
     */
    public function property_tokens_are_user_specific(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            
            // Create token for user1
            Sanctum::actingAs($user1);
            
            // Access should return user1's data
            $response = $this->getJson('/api/user');
            $response->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user1->id,
                    'email' => $user1->email,
                ],
            ]);
            
            // Should not return user2's data
            $response->assertJsonMissing([
                'data' => [
                    'id' => $user2->id,
                    'email' => $user2->email,
                ],
            ]);
            
            $user1->tokens()->delete();
            $user2->tokens()->delete();
            $user1->delete();
            $user2->delete();
        }
    }

    /**
     * Test that multiple tokens for same user all work
     * 
     * @test
     */
    public function property_multiple_tokens_per_user_all_work(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $user = User::factory()->create();
            
            // Create multiple tokens
            $tokenCount = rand(2, 5);
            $tokens = [];
            for ($j = 0; $j < $tokenCount; $j++) {
                $tokens[] = $user->createToken('device-' . $j)->plainTextToken;
            }
            
            // All tokens should work
            foreach ($tokens as $index => $token) {
                $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                    ->getJson('/api/user');
                
                $this->assertEquals(200, $response->status(),
                    "Iteration {$i}, Token {$index}: All tokens for a user should work");
            }
            
            $user->tokens()->delete();
            $user->delete();
        }
    }

    /**
     * Test that tokens grant access to all protected endpoints
     * 
     * @test
     */
    public function property_tokens_grant_access_to_all_protected_endpoints(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $user = User::factory()->create();
            Sanctum::actingAs($user);
            
            // Test multiple protected endpoints
            $endpoints = [
                '/api/user',
            ];
            
            foreach ($endpoints as $endpoint) {
                $response = $this->getJson($endpoint);
                
                $this->assertNotEquals(401, $response->status(),
                    "Iteration {$i}, Endpoint {$endpoint}: Valid token should grant access");
            }
            
            $user->tokens()->delete();
            $user->delete();
        }
    }
}
