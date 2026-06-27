<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcosystemTokenIssueTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_issue_ecosystem_handoff_token()
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->post('/api/ecosystem/token');

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'expires_at']);
        $this->assertNotEmpty($response->json('token'));
        $this->assertNotEmpty($response->json('expires_at'));
    }

    public function test_guest_cannot_issue_ecosystem_handoff_token()
    {
        $response = $this->post('/api/ecosystem/token');

        $response->assertStatus(302);
    }
}
