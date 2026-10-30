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
            ->assertSee('会員総数')
            ->assertSee('有効会員数')
            ->assertSee('停止中会員数')
            ->assertSee('メール認証状態')
            ->assertSee('利用状態')
            ->assertSee('name="_token"', false)
            ->assertDontSee('unrelated@example.test');
    }

    public function test_admin_search_displays_a_specific_message_when_no_members_match(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['search' => 'no-such-member']))
            ->assertOk()
            ->assertSee('検索条件に一致する会員はいません。');
    }

    public function test_admin_summary_shows_total_active_and_suspended_member_counts(): void
    {
        $admin = User::factory()->administrator()->create();
        User::factory()->create();
        User::factory()->suspended()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('会員総数')
            ->assertSee('<strong>3</strong>', false)
            ->assertSee('有効会員数')
            ->assertSee('<strong>2</strong>', false)
            ->assertSee('停止中会員数')
            ->assertSee('<strong>1</strong>', false);
    }

    public function test_admin_can_view_member_details_and_only_that_members_activity(): void
    {
        $admin = User::factory()->administrator()->create();
        $member = User::factory()->unverified()->create([
            'name' => '詳細確認会員',
            'email' => 'member-detail@example.test',
            'member_type' => 'business',
        ]);
        $otherMember = User::factory()->create(['email' => 'other-detail@example.test']);
        DB::table('admin_activity_logs')->insert([
            [
                'actor_user_id' => $admin->id,
                'target_user_id' => $member->id,
                'action' => 'member.suspended',
                'details' => json_encode(['from' => 'active', 'to' => 'suspended']),
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actor_user_id' => $admin->id,
                'target_user_id' => $otherMember->id,
                'action' => 'member.activated',
                'details' => json_encode(['from' => 'suspended', 'to' => 'active']),
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.members.show', $member))
            ->assertOk()
            ->assertSee((string) $member->id)
            ->assertSee($member->name)
            ->assertSee($member->email)
            ->assertSee('銃砲店会員')
            ->assertSee('未認証')
            ->assertSee('管理者権限')
            ->assertSee('利用停止')
            ->assertSee('会員一覧へ戻る')
            ->assertSee('会員停止')
            ->assertDontSee('会員再開')
            ->assertDontSee($otherMember->email);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.members.show', $member));
    }

    public function test_only_verified_admins_can_view_member_details_and_missing_members_return_404(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->administrator()->create();
        $unverifiedAdmin = User::factory()->administrator()->unverified()->create();

        $this->get(route('admin.members.show', $member))->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.members.show', $member))->assertForbidden();
        $this->actingAs($unverifiedAdmin)->get(route('admin.members.show', $member))
            ->assertRedirect(route('verification.notice'));
        $this->actingAs($admin)->get(route('admin.members.show', 999999))->assertNotFound();
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
