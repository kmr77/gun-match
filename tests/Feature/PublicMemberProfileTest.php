<?php

namespace Tests\Feature;

use App\Enums\MemberType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMemberProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_shows_only_the_display_name_and_member_type(): void
    {
        $member = User::factory()->create([
            'name' => '公開表示名',
            'member_type' => MemberType::GunShop,
            'legal_name' => '非公開本名',
            'birth_date' => '1980-01-02',
            'phone' => '+819012345678',
            'email' => 'private-profile@example.test',
            'has_firearm_permit' => true,
            'verification_note' => '非公開の審査メモ',
        ]);

        $this->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('公開表示名')
            ->assertSee('銃砲店会員')
            ->assertSee('表示名')
            ->assertSee('会員区分')
            ->assertDontSee('非公開本名')
            ->assertDontSee('1980年')
            ->assertDontSee('+819012345678')
            ->assertDontSee('private-profile@example.test')
            ->assertDontSee('所持している')
            ->assertDontSee('非公開の審査メモ');
    }

    public function test_public_profile_is_accessible_without_login_and_uses_the_logged_in_member_profile_link(): void
    {
        $member = User::factory()->create(['name' => '公開会員']);

        $this->get(route('members.show', $member))->assertOk();
        $this->actingAs($member)->get(route('mypage.profile.public'))
            ->assertOk()
            ->assertSee(route('members.show', $member))
            ->assertSee('表示名と会員区分のみ')
            ->assertDontSee('第三者向けプロフィール公開機能は未実装');
    }

    public function test_public_profile_hides_unverified_or_suspended_accounts_and_unknown_members(): void
    {
        $unverifiedEmail = User::factory()->unverified()->create();
        $unverifiedPhone = User::factory()->create(['phone_verified_at' => null]);
        $suspended = User::factory()->suspended()->create();

        $this->get(route('members.show', $unverifiedEmail))->assertNotFound();
        $this->get(route('members.show', $unverifiedPhone))->assertNotFound();
        $this->get(route('members.show', $suspended))->assertNotFound();
        $this->get('/members/999999999')->assertNotFound();
    }
}
