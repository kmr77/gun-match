<?php

namespace Tests\Feature;

use App\Enums\MemberType;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationProfileSpecificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_private_identity_fields_and_an_adult_birth_date(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('利用規約')
            ->assertSee(route('terms'), false)
            ->assertSee(route('privacy'), false)
            ->assertSee('東京都')
            ->assertSee('正式な規約への同意取得・記録は行っていません。')
            ->assertDontSee('name="terms_version"', false);

        $this->from(route('register'))->post(route('register'), $this->registrationData([
            'birth_date' => now()->subYears(17)->toDateString(),
        ]))->assertRedirect(route('register'))->assertSessionHasErrors(['birth_date']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_does_not_record_fictional_consent_for_unpublished_terms(): void
    {
        $this->from(route('register'))->post(route('register'), $this->registrationData([
            'terms_version' => '2026-11-12-v1',
        ]))->assertRedirect(route('register'))
            ->assertSessionHasErrors('terms_version');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('terms_consents', 0);
    }

    public function test_registration_requires_explicit_consent_to_the_published_terms_version(): void
    {
        $this->publishTerms('terms-2026-11-v1');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="terms_version"', false)
            ->assertSee('利用規約（terms-2026-11-v1）に同意します');

        $this->from(route('register'))->followingRedirects()
            ->post(route('register'), $this->registrationData())
            ->assertOk()
            ->assertSee('利用規約への同意が必要です。');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('terms_consents', 0);
    }

    public function test_formal_terms_must_have_publishable_content_before_registration_consent_is_enabled(): void
    {
        config([
            'gunmatch_legal.terms.published' => true,
            'gunmatch_legal.terms.version' => 'terms-2026-11-v1',
            'gunmatch_legal.terms.effective_at' => now()->subMinute()->toIso8601String(),
            'gunmatch_legal.terms.sections' => [],
        ]);

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('name="terms_version"', false)
            ->assertSee('正式な規約への同意取得・記録は行っていません。');

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('正式な利用規約は公開前確認中です。')
            ->assertDontSee('terms-2026-11-v1');
    }

    public function test_registration_records_the_exact_terms_version_and_explicit_checkbox_action(): void
    {
        $this->publishTerms('terms-2026-11-v1');

        $this->post(route('register'), $this->registrationData([
            'terms_version' => 'terms-2026-11-v1',
        ]))->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'member@example.test')->firstOrFail();
        $this->assertDatabaseHas('terms_consents', [
            'user_id' => $user->id,
            'terms_version' => 'terms-2026-11-v1',
            'consent_method' => 'registration_checkbox',
        ]);
        $this->assertNotNull($user->termsConsents()->sole()->consented_at);
        $this->assertSame(1, $user->termsConsents()->count());
    }

    public function test_terms_history_is_not_overwritten_when_a_new_version_is_published(): void
    {
        $this->publishTerms('terms-2026-11-v1');
        $this->post(route('register'), $this->registrationData([
            'terms_version' => 'terms-2026-11-v1',
        ]))->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'member@example.test')->firstOrFail();
        $consentedAt = $user->termsConsents()->sole()->consented_at;

        $this->publishTerms('terms-2026-12-v2');
        $this->actingAs($user)->get(route('terms'))
            ->assertOk()
            ->assertSee('terms-2026-12-v2');
        $this->actingAs($user)->get(route('mypage.profile'))->assertOk();

        $this->assertDatabaseHas('terms_consents', [
            'user_id' => $user->id,
            'terms_version' => 'terms-2026-11-v1',
            'consented_at' => $consentedAt->format('Y-m-d H:i:s'),
        ]);
        $this->assertSame(1, $user->termsConsents()->count());
    }

    public function test_privacy_policy_publication_does_not_create_privacy_consent_records(): void
    {
        config([
            'gunmatch_legal.privacy.published' => true,
            'gunmatch_legal.privacy.version' => 'privacy-2026-11-v1',
            'gunmatch_legal.privacy.effective_at' => now()->subMinute()->toIso8601String(),
            'gunmatch_legal.privacy.sections' => [
                ['title' => '個人情報の取扱い', 'paragraphs' => ['公開用の確認済み本文。']],
            ],
        ]);

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('privacy-2026-11-v1')
            ->assertSee('公開用の確認済み本文。');

        $this->post(route('register'), $this->registrationData())
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('terms_consents', 0);
    }

    public function test_registration_rejects_a_forged_or_outdated_terms_version(): void
    {
        $this->publishTerms('terms-2026-11-v2');

        $this->from(route('register'))->post(route('register'), $this->registrationData([
            'terms_version' => 'terms-2026-11-v1',
        ]))->assertRedirect(route('register'))
            ->assertSessionHasErrors('terms_version');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('terms_consents', 0);
    }

    public function test_registration_consent_is_not_enabled_before_the_terms_effective_date(): void
    {
        config([
            'gunmatch_legal.terms.published' => true,
            'gunmatch_legal.terms.version' => 'terms-2026-11-v1',
            'gunmatch_legal.terms.effective_at' => now()->addDay()->toIso8601String(),
            'gunmatch_legal.terms.sections' => [
                ['title' => '利用条件', 'paragraphs' => ['確認済みの公開本文。']],
            ],
        ]);

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('name="terms_version"', false);
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('正式な利用規約は公開前確認中です。');
    }

    public function test_existing_members_are_not_backfilled_with_a_fictional_terms_consent(): void
    {
        $existingMember = User::factory()->create();
        $this->publishTerms('terms-2026-11-v1');

        $this->assertSame(0, $existingMember->termsConsents()->count());
        $this->assertDatabaseCount('terms_consents', 0);
    }

    public function test_registration_requires_identity_name_email_prefecture_and_birth_date(): void
    {
        $this->from(route('register'))->post(route('register'), [
            'member_type' => MemberType::Individual->value,
            'password' => 'GunMatch-Test!234',
            'password_confirmation' => 'GunMatch-Test!234',
        ])->assertRedirect(route('register'))->assertSessionHasErrors([
            'legal_name',
            'name',
            'email',
            'prefecture',
            'birth_date',
            'phone',
        ]);
    }

    public function test_registration_accepts_a_member_who_is_exactly_eighteen(): void
    {
        $this->post(route('register'), $this->registrationData([
            'birth_date' => now()->subYears(18)->toDateString(),
        ]))->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_saves_private_details_and_keeps_permit_as_a_self_declaration(): void
    {
        $this->post(route('register'), $this->registrationData([
            'has_firearm_permit' => '1',
            'phone' => '03-1234-5678',
        ]))->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'member@example.test')->firstOrFail();

        $this->assertSame(MemberType::Individual, $user->member_type);
        $this->assertTrue($user->has_firearm_permit);
        $this->assertNull($user->phone_verified_at);
        $this->assertNull($user->email_verified_at);
        $this->assertArrayNotHasKey('legal_name', $user->toArray());
        $this->assertArrayNotHasKey('email', $user->toArray());
        $this->assertArrayNotHasKey('email_verified_at', $user->toArray());
        $this->assertArrayNotHasKey('birth_date', $user->toArray());
        $this->assertArrayNotHasKey('phone', $user->toArray());
        $this->assertArrayNotHasKey('prefecture', $user->toArray());
        $this->assertArrayNotHasKey('has_firearm_permit', $user->toArray());
    }

    public function test_gun_shop_registration_keeps_member_type_separate_from_admin_and_permit_verification(): void
    {
        $this->post(route('register'), $this->registrationData([
            'member_type' => MemberType::GunShop->value,
            'has_firearm_permit' => '0',
            'is_admin' => true,
            'verification_status' => 'verified',
        ]))->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'member@example.test')->firstOrFail();

        $this->assertSame(MemberType::GunShop, $user->member_type);
        $this->assertFalse($user->has_firearm_permit);
        $this->assertFalse($user->is_admin);
        $this->assertSame('unverified', $user->verification_status->value);
    }

    public function test_profile_edit_updates_only_the_authenticated_members_private_profile_and_audits_field_names(): void
    {
        $user = User::factory()->create([
            'name' => '表示名',
            'email' => 'member@example.test',
            'is_admin' => true,
            'legal_name' => '旧本名',
            'prefecture' => '東京都',
            'birth_date' => '1990-01-01',
            'phone' => '03-1111-1111',
            'has_firearm_permit' => false,
        ]);
        $other = User::factory()->create(['email' => 'other@example.test']);

        $this->actingAs($user)->put(route('mypage.profile.update'), [
            ...$this->profileData($user),
            'legal_name' => '新しい本名',
            'name' => '新しい表示名',
            'prefecture' => '京都府',
            'birth_date' => '1989-12-31',
            'phone' => '075-1234-5678',
            'has_firearm_permit' => '1',
            'phone_verified_at' => now(),
            'member_type' => MemberType::GunShop->value,
            'is_admin' => false,
            'user_id' => $other->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $user = $user->fresh();
        $this->assertSame('新しい本名', $user->legal_name);
        $this->assertSame('新しい表示名', $user->name);
        $this->assertSame('京都府', $user->prefecture);
        $this->assertSame('1989-12-31', $user->birth_date->toDateString());
        $this->assertSame('075-1234-5678', $user->phone);
        $this->assertNull($user->phone_verified_at);
        $this->assertTrue($user->has_firearm_permit);
        $this->assertSame(MemberType::Individual, $user->member_type);
        $this->assertTrue($user->is_admin);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'email' => 'other@example.test']);

        $log = AdminActivityLog::query()->where('action', 'member.profile_updated')->sole();
        $this->assertSame($user->id, $log->actor_user_id);
        $this->assertSame($user->id, $log->target_user_id);
        $this->assertContains('legal_name', $log->details['changed_fields']);
        $this->assertContains('name', $log->details['changed_fields']);
        $this->assertContains('prefecture', $log->details['changed_fields']);
        $this->assertContains('birth_date', $log->details['changed_fields']);
        $this->assertContains('phone', $log->details['changed_fields']);
        $this->assertStringNotContainsString('新しい本名', json_encode($log->details, JSON_THROW_ON_ERROR));
    }

    public function test_underage_and_invalid_profile_data_are_rejected_without_changing_existing_data(): void
    {
        $user = User::factory()->create([
            'legal_name' => '保持する本名',
            'prefecture' => '東京都',
            'birth_date' => '1990-01-01',
        ]);

        $this->actingAs($user)->from(route('mypage.profile'))->put(route('mypage.profile.update'), [
            ...$this->profileData($user),
            'birth_date' => now()->subYears(17)->toDateString(),
            'prefecture' => '架空県',
            'has_firearm_permit' => 'yes',
        ])->assertRedirect(route('mypage.profile'))->assertSessionHasErrors([
            'birth_date',
            'prefecture',
            'has_firearm_permit',
        ]);

        $this->assertSame('保持する本名', $user->fresh()->legal_name);
        $this->assertDatabaseCount('admin_activity_logs', 0);
    }

    public function test_private_profile_fields_are_not_rendered_in_the_public_profile_sample(): void
    {
        $user = User::factory()->create([
            'name' => '公開用表示名',
            'email' => 'private@example.test',
            'legal_name' => '機密の本名',
            'prefecture' => '東京都',
            'birth_date' => '1990-01-01',
            'phone' => '03-1234-5678',
        ]);

        $this->actingAs($user)->get(route('mypage.profile.public'))
            ->assertOk()
            ->assertSee('公開用表示名')
            ->assertDontSee('private@example.test')
            ->assertDontSee('機密の本名')
            ->assertDontSee('03-1234-5678')
            ->assertDontSee('東京都');
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationData(array $overrides = []): array
    {
        return array_replace([
            'member_type' => MemberType::Individual->value,
            'legal_name' => '会員本名',
            'name' => '会員表示名',
            'email' => 'member@example.test',
            'phone' => '+819012345678',
            'prefecture' => '東京都',
            'birth_date' => now()->subYears(30)->toDateString(),
            'password' => 'GunMatch-Test!234',
            'password_confirmation' => 'GunMatch-Test!234',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function profileData(User $user): array
    {
        return [
            'legal_name' => $user->legal_name ?? '会員本名',
            'name' => $user->name,
            'email' => $user->email,
            'prefecture' => $user->prefecture ?? '東京都',
            'birth_date' => $user->birth_date?->toDateString() ?? '1990-01-01',
            'phone' => $user->phone,
            'has_firearm_permit' => $user->has_firearm_permit === null
                ? null
                : ($user->has_firearm_permit ? '1' : '0'),
        ];
    }

    private function publishTerms(string $version): void
    {
        config([
            'gunmatch_legal.terms.published' => true,
            'gunmatch_legal.terms.version' => $version,
            'gunmatch_legal.terms.effective_at' => now()->subMinute()->toIso8601String(),
            'gunmatch_legal.terms.sections' => [
                ['title' => '利用条件', 'paragraphs' => ['確認済みの公開本文。']],
            ],
        ]);
    }
}
