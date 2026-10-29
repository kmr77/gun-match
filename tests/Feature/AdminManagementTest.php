<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_members_and_view_account_information(): void
    {
        $admin = User::factory()->administrator()->create();
        $match = User::factory()->unverified()->create([
            'name' => '検索対象',
            'email' => 'SearchTarget@example.test',
            'member_type' => 'business',
        ]);
        User::factory()->create(['name' => '別会員', 'email' => 'unrelated@example.test']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['search' => 'SEARCHTARGET']))
            ->assertOk()
            ->assertSee($match->email)
            ->assertSee('銃砲店会員')
            ->assertSee('未認証')
            ->assertSee('name="_token"', false)
            ->assertDontSee('unrelated@example.test');
    }

    public function test_only_an_active_verified_admin_can_search_or_change_member_status(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->administrator()->create();
        $suspendedAdmin = User::factory()->administrator()->suspended()->create();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($suspendedAdmin)->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_can_suspend_and_reactivate_members_and_actions_are_audited(): void
    {
        $admin = User::factory()->administrator()->create();
        $member = User::factory()->create();
        $rememberToken = $member->remember_token;
        DB::table('sessions')->insert([
            'id' => 'member-device-session',
            'user_id' => $member->id,
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->patch(route('admin.members.status.update', $member), [
            'status' => 'suspended',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertFalse($member->fresh()->is_active);
        $this->assertNotSame($rememberToken, $member->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'member-device-session']);
        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $member->id,
            'action' => 'member.suspended',
        ]);

        $this->patch(route('admin.members.status.update', $member), [
            'status' => 'active',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertTrue($member->fresh()->is_active);
        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $member->id,
            'action' => 'member.activated',
        ]);
    }

    public function test_suspended_members_cannot_log_in_and_existing_sessions_are_invalidated(): void
    {
        $member = User::factory()->create([
            'email' => 'suspended@example.test',
            'password' => 'GunMatch-Suspended!234',
        ]);
        $member->forceFill(['is_active' => false])->save();

        $this->from(route('login'))->post(route('login'), [
            'email' => $member->email,
            'password' => 'GunMatch-Suspended!234',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($member)->get(route('mypage'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_member_cannot_suspend_themselves_and_status_input_is_validated(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->patch(route('admin.members.status.update', $admin), [
            'status' => 'suspended',
        ])->assertRedirect()->assertSessionHasErrors('status');

        $this->patch(route('admin.members.status.update', User::factory()->create()), [
            'status' => 'admin',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseCount('admin_activity_logs', 0);
    }

    public function test_cli_command_grants_and_revokes_admin_with_confirmation_and_audit(): void
    {
        $member = User::factory()->create(['email' => 'operator-target@example.test']);
        User::factory()->administrator()->create(['email' => 'remaining-admin@example.test']);

        $this->artisan('gunmatch:admin', ['email' => $member->email])
            ->expectsConfirmation("{$member->email} の管理者権限を付与しますか？", 'yes')
            ->assertExitCode(0);

        $this->assertTrue($member->fresh()->is_admin);
        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_label' => 'CLI operator',
            'target_user_id' => $member->id,
            'action' => 'admin.granted',
        ]);

        $this->artisan('gunmatch:admin', ['email' => $member->email, '--revoke' => true])
            ->expectsConfirmation("{$member->email} の管理者権限を解除しますか？", 'yes')
            ->assertExitCode(0);

        $this->assertFalse($member->fresh()->is_admin);
        $this->assertDatabaseHas('admin_activity_logs', [
            'target_user_id' => $member->id,
            'action' => 'admin.revoked',
        ]);
    }

    public function test_cli_command_cannot_remove_the_last_active_admin(): void
    {
        $admin = User::factory()->administrator()->create(['email' => 'sole-admin@example.test']);

        $this->artisan('gunmatch:admin', ['email' => $admin->email, '--revoke' => true])
            ->expectsConfirmation("{$admin->email} の管理者権限を解除しますか？", 'yes')
            ->assertExitCode(1);

        $this->assertTrue($admin->fresh()->is_admin);
        $this->assertDatabaseCount('admin_activity_logs', 0);
    }
}
