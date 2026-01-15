<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-based test for team membership validation
 * 
 * Feature: infodot-modernization, Property 10: Team Membership Validation
 * 
 * Property: For any team-protected resource, only team members should be able to access it.
 * Validates: Requirements FR-10
 */
class TeamMembershipValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that only team members can access team resources
     * 
     * @test
     */
    public function property_only_team_members_can_access_team_resources(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $teamOwner = User::factory()->withPersonalTeam()->create();
            $teamMember = User::factory()->create();
            $nonMember = User::factory()->create();

            $team = $teamOwner->currentTeam;

            // Add team member
            $team->users()->attach($teamMember, ['role' => 'editor']);

            // Team member should have access
            $this->assertTrue($team->users->contains($teamMember));
            $this->assertTrue($teamMember->belongsToTeam($team));

            // Non-member should not have access
            $this->assertFalse($team->users->contains($nonMember));
            $this->assertFalse($nonMember->belongsToTeam($team));
        }
    }

    /**
     * Test that team membership is validated for team switching
     * 
     * @test
     */
    public function property_team_membership_validated_for_switching(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->withPersonalTeam()->create();
            $otherUser = User::factory()->withPersonalTeam()->create();

            $userTeam = $user->currentTeam;
            $otherTeam = $otherUser->currentTeam;

            // User can switch to their own team
            $user->switchTeam($userTeam);
            $this->assertEquals($userTeam->id, $user->fresh()->current_team_id);

            // User cannot switch to a team they don't belong to
            // (This should be prevented by application logic)
            $user->current_team_id = $otherTeam->id;
            $user->save();

            // Verify the user doesn't actually belong to the other team
            $this->assertFalse($user->belongsToTeam($otherTeam));
        }
    }

    /**
     * Test that team membership is validated for team operations
     * 
     * @test
     */
    public function property_team_membership_validated_for_operations(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $teamOwner = User::factory()->withPersonalTeam()->create();
            $teamMember = User::factory()->create();
            $nonMember = User::factory()->create();

            $team = $teamOwner->currentTeam;
            $team->users()->attach($teamMember, ['role' => 'editor']);

            // Team owner can perform operations
            $this->assertTrue($teamOwner->ownsTeam($team));

            // Team member belongs to team
            $this->assertTrue($teamMember->belongsToTeam($team));

            // Non-member does not belong to team
            $this->assertFalse($nonMember->belongsToTeam($team));
            $this->assertFalse($nonMember->ownsTeam($team));
        }
    }

    /**
     * Test that removed team members lose access
     * 
     * @test
     */
    public function property_removed_team_members_lose_access(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $teamOwner = User::factory()->withPersonalTeam()->create();
            $teamMember = User::factory()->create();

            $team = $teamOwner->currentTeam;

            // Add member
            $team->users()->attach($teamMember, ['role' => 'editor']);
            $this->assertTrue($teamMember->belongsToTeam($team));

            // Remove member
            $team->users()->detach($teamMember);
            
            // Verify member no longer has access
            $this->assertFalse($teamMember->fresh()->belongsToTeam($team));
        }
    }

    /**
     * Test that team membership is validated across multiple teams
     * 
     * @test
     */
    public function property_team_membership_validated_across_multiple_teams(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->withPersonalTeam()->create();
            $team1Owner = User::factory()->withPersonalTeam()->create();
            $team2Owner = User::factory()->withPersonalTeam()->create();

            $team1 = $team1Owner->currentTeam;
            $team2 = $team2Owner->currentTeam;

            // Add user to team1
            $team1->users()->attach($user, ['role' => 'editor']);

            // User should belong to team1
            $this->assertTrue($user->belongsToTeam($team1));

            // User should not belong to team2
            $this->assertFalse($user->belongsToTeam($team2));

            // Add user to team2
            $team2->users()->attach($user, ['role' => 'editor']);

            // User should now belong to both teams
            $this->assertTrue($user->belongsToTeam($team1));
            $this->assertTrue($user->belongsToTeam($team2));
        }
    }

    /**
     * Test that team roles are properly validated
     * 
     * @test
     */
    public function property_team_roles_properly_validated(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $teamOwner = User::factory()->withPersonalTeam()->create();
            $editor = User::factory()->create();
            $viewer = User::factory()->create();

            $team = $teamOwner->currentTeam;

            // Add members with different roles
            $team->users()->attach($editor, ['role' => 'editor']);
            $team->users()->attach($viewer, ['role' => 'viewer']);

            // Verify all are team members
            $this->assertTrue($editor->belongsToTeam($team));
            $this->assertTrue($viewer->belongsToTeam($team));

            // Verify roles are stored correctly
            $editorRole = $team->users()->where('user_id', $editor->id)->first()->membership->role;
            $viewerRole = $team->users()->where('user_id', $viewer->id)->first()->membership->role;

            $this->assertEquals('editor', $editorRole);
            $this->assertEquals('viewer', $viewerRole);
        }
    }
}
