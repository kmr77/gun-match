<?php

namespace Tests\Feature;

use App\Enums\MemberVerificationStatus;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMemberVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_members_start_unverified_and_admin_can_verify_with_method_and_date(): void
    {
        $admin = User::factory()->administrator()->create();
        $member = User::factory()->create(['member_type' => 'individual']);
        $member->refresh();

        $this->assertSame(MemberVerificationStatus::Unverified, $member->verification_status);
        $this->assertNull($member->verification_type);
        $this->assertArrayNotHasKey('verification_status', $member->toArray());
        $this->assertArrayNotHasKey('verification_note', $member->toArray());

        $this->actingAs($admin)->get(route('admin.members.show', $member))
            ->assertOk()
            ->assertSee('会員確認・審査')
            ->assertSee('未確認')
            ->assertSee('本人確認')
            ->assertSee('確認状態の変更履歴はありません。')
            ->assertSee('証明書番号・本人確認書類の画像は保存しません。');

        $this->actingAs($admin)->patch(route('admin.members.verification.update', $member), [
            'verification_status' => 'verified',
            'verification_type' => 'identity',
            'verification_method' => '対面で本人確認を実施',
            'verified_at' => now()->toDateString(),
            'verification_note' => '管理者だけが閲覧する審査メモです。',
        ])->assertRedirect(route('admin.members.show', $member))
            ->assertSessionHas('status');

        $member->refresh();
        $this->assertSame(MemberVerificationStatus::Verified, $member->verification_status);
        $this->assertSame('identity', $member->verification_type->value);
        $this->assertSame('対面で本人確認を実施', $member->verification_method);
        $this->assertSame(now()->toDateString(), $member->verified_at->toDateString());
        $this->assertSame('管理者だけが閲覧する審査メモです。', $member->verification_note);
        $this->assertSame($admin->id, $member->reviewed_by_user_id);
        $this->assertNotNull($member->reviewed_at);

        $history = AdminActivityLog::query()->where('action', 'member.verification_updated')->firstOrFail();
        $this->assertSame($admin->id, $history->actor_user_id);
        $this->assertSame($member->id, $history->target_user_id);
        $this->assertSame('unverified', $history->details['from_status']);
        $this->assertSame('verified', $history->details['to_status']);
        $this->assertSame('identity', $history->details['to_type']);
        $this->assertTrue($history->details['verification_method_recorded']);
        $this->assertTrue($history->details['verification_date_recorded']);
        $this->assertTrue($history->details['note_changed']);
        $this->assertArrayNotHasKey('verification_method', $history->details);
        $this->assertArrayNotHasKey('verification_note', $history->details);
        $this->assertArrayNotHasKey('certificate_number', $history->details);

        $this->actingAs($admin)->get(route('admin.members.show', $member))
            ->assertOk()
            ->assertSee('審査担当者')
            ->assertSee($admin->name)
            ->assertSee('確認済み')
            ->assertSee('対面で本人確認を実施')
            ->assertSee('管理者だけが閲覧する審査メモです。')
            ->assertSee('確認方法・確認日を記録');
    }

    public function test_confirmation_requires_actual_method_and_past_or_current_date(): void
    {
        $admin = User::factory()->administrator()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)->from(route('admin.members.show', $member))
            ->patch(route('admin.members.verification.update', $member), [
                'verification_status' => 'verified',
                'verification_type' => 'identity',
                'verification_method' => '',
                'verified_at' => '',
            ])
            ->assertRedirect(route('admin.members.show', $member))
            ->assertSessionHasErrors(['verification_method', 'verified_at']);

        $this->patch(route('admin.members.verification.update', $member), [
            'verification_status' => 'verified',
            'verification_type' => 'identity',
            'verification_method' => '対面確認',
            'verified_at' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors('verified_at');

        $this->assertSame(MemberVerificationStatus::Unverified, $member->fresh()->verification_status);
        $this->assertDatabaseCount('admin_activity_logs', 0);
    }

    public function test_verification_type_must_match_member_type_and_status_can_be_returned(): void
    {
        $admin = User::factory()->administrator()->create();
        $businessMember = User::factory()->create(['member_type' => 'business']);

        $this->actingAs($admin)->patch(route('admin.members.verification.update', $businessMember), [
            'verification_status' => 'pending',
            'verification_type' => 'identity',
        ])->assertSessionHasErrors('verification_type');

        $this->patch(route('admin.members.verification.update', $businessMember), [
            'verification_status' => 'pending',
            'verification_type' => 'business',
            'verification_note' => '確認資料の追加提出を依頼。',
        ])->assertRedirect(route('admin.members.show', $businessMember));

        $this->assertSame(MemberVerificationStatus::Pending, $businessMember->fresh()->verification_status);
        $this->assertSame('business', $businessMember->fresh()->verification_type->value);

        $this->patch(route('admin.members.verification.update', $businessMember), [
            'verification_status' => 'returned',
            'verification_type' => 'business',
            'verification_note' => '記載内容の修正を依頼。',
        ])->assertRedirect(route('admin.members.show', $businessMember));

        $this->assertSame(MemberVerificationStatus::Returned, $businessMember->fresh()->verification_status);
        $this->assertSame(2, AdminActivityLog::query()->where('action', 'member.verification_updated')->count());
    }

    public function test_only_verified_admins_can_manage_verification_and_members_cannot_change_their_status(): void
    {
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $admin = User::factory()->administrator()->create();
        $unverifiedAdmin = User::factory()->administrator()->unverified()->create();

        $this->patch(route('admin.members.verification.update', $member), [
            'verification_status' => 'verified',
            'verification_type' => 'identity',
            'verification_method' => '本人申告',
            'verified_at' => now()->toDateString(),
        ])->assertRedirect(route('login'));

        $this->actingAs($otherMember)->patch(route('admin.members.verification.update', $member), [
            'verification_status' => 'verified',
            'verification_type' => 'identity',
            'verification_method' => '本人申告',
            'verified_at' => now()->toDateString(),
        ])->assertForbidden();

        $this->actingAs($unverifiedAdmin)
            ->patch(route('admin.members.verification.update', $member), [
                'verification_status' => 'verified',
                'verification_type' => 'identity',
                'verification_method' => '本人申告',
                'verified_at' => now()->toDateString(),
            ])->assertRedirect(route('verification.notice'));

        $this->actingAs($member)->put(route('mypage.profile.update'), [
            'name' => $member->name,
            'email' => $member->email,
            'verification_status' => 'verified',
            'verification_type' => 'identity',
            'verification_method' => '自己確認',
            'verification_note' => '本人が設定したメモ',
        ])->assertRedirect();

        $this->assertSame(MemberVerificationStatus::Unverified, $member->fresh()->verification_status);
        $this->assertNull($member->fresh()->verification_note);
        $this->actingAs($admin)->get(route('mypage'))->assertOk()
            ->assertDontSee('自己確認')
            ->assertDontSee('本人が設定したメモ');
    }
}
