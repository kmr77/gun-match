<?php

namespace Tests\Feature;

use App\Models\SmsVerificationChallenge;
use App\Models\User;
use App\Services\Sms\SmsDeliveryException;
use App\Services\Sms\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_members_can_complete_profile_and_phone_verification_but_not_use_member_pages(): void
    {
        $user = User::factory()->create(['phone_verified_at' => null]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertRedirect(route('phone-verification.notice'));

        $this->get(route('phone-verification.notice'))->assertOk();
        $this->get(route('mypage.profile'))->assertOk();

        $withoutPhone = User::factory()->create(['phone' => null, 'phone_verified_at' => null]);
        $this->actingAs($withoutPhone)->get(route('mypage'))
            ->assertRedirect(route('mypage.profile'));
        $this->get(route('mypage.profile'))->assertOk();
    }

    public function test_login_routes_members_to_their_next_required_verification_step(): void
    {
        $unverifiedEmail = User::factory()->unverified()->create(['password' => 'GunMatch-Login!234']);
        $this->post(route('login'), [
            'email' => $unverifiedEmail->email,
            'password' => 'GunMatch-Login!234',
        ])->assertRedirect(route('verification.notice'));

        $this->post(route('logout'));
        $withoutPhone = User::factory()->create([
            'phone' => null,
            'phone_verified_at' => null,
            'password' => 'GunMatch-Login!234',
        ]);
        $this->post(route('login'), [
            'email' => $withoutPhone->email,
            'password' => 'GunMatch-Login!234',
        ])->assertRedirect(route('mypage.profile'));

        $this->post(route('logout'));
        $withoutSms = User::factory()->create(['phone_verified_at' => null, 'password' => 'GunMatch-Login!234']);
        $this->post(route('login'), [
            'email' => $withoutSms->email,
            'password' => 'GunMatch-Login!234',
        ])->assertRedirect(route('phone-verification.notice'));
    }

    public function test_email_unverified_users_are_routed_to_email_verification_before_phone_verification(): void
    {
        $user = User::factory()->unverified()->create(['phone_verified_at' => null]);

        $this->actingAs($user)
            ->get(route('phone-verification.notice'))
            ->assertRedirect(route('verification.notice'));
        $this->get(route('mypage.profile'))->assertOk();
    }

    public function test_verified_phone_cannot_trigger_an_unnecessary_sms_send(): void
    {
        $gateway = new TestSmsGateway;
        $this->app->instance(SmsGateway::class, $gateway);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('mypage.profile'))
            ->post(route('phone-verification.send'))
            ->assertRedirect(route('mypage.profile'))
            ->assertSessionHasErrors('phone');

        $this->assertSame('', $gateway->message);
        $this->assertDatabaseCount('sms_verification_challenges', 0);
    }

    public function test_sms_code_is_hashed_short_lived_and_usable_only_once(): void
    {
        $gateway = new TestSmsGateway;
        $this->app->instance(SmsGateway::class, $gateway);
        $user = User::factory()->create(['phone_verified_at' => null]);

        $this->actingAs($user)
            ->post(route('phone-verification.send'))
            ->assertRedirect();

        $challenge = SmsVerificationChallenge::query()->sole();
        preg_match('/は(\d{6})です/', $gateway->message, $matches);
        $code = $matches[1];

        $this->assertNotSame($code, $challenge->code_hash);
        $this->assertTrue(Hash::check($code, $challenge->code_hash));
        $this->assertSame(1, $challenge->sends_in_window);

        $this->post(route('phone-verification.verify'), ['code' => $code])
            ->assertRedirect(route('mypage'));
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertNull($challenge->fresh()->code_hash);
        $this->assertNull($challenge->fresh()->phone_hash);

        $this->from(route('phone-verification.notice'))
            ->post(route('phone-verification.verify'), ['code' => $code])
            ->assertRedirect(route('phone-verification.notice'))
            ->assertSessionHasErrors('code');
    }

    public function test_expired_code_is_rejected_and_old_codes_are_invalidated_on_resend(): void
    {
        Carbon::setTestNow(now());
        $gateway = new TestSmsGateway;
        $this->app->instance(SmsGateway::class, $gateway);
        $user = User::factory()->create(['phone_verified_at' => null]);
        $this->actingAs($user)->post(route('phone-verification.send'))->assertRedirect();
        preg_match('/は(\d{6})です/', $gateway->message, $firstMatches);
        $firstCode = $firstMatches[1];

        $this->from(route('phone-verification.notice'))
            ->post(route('phone-verification.send'))
            ->assertRedirect(route('phone-verification.notice'))
            ->assertSessionHasErrors('phone');

        Carbon::setTestNow(now()->addSeconds(61));
        $this->post(route('phone-verification.send'))->assertRedirect();
        preg_match('/は(\d{6})です/', $gateway->message, $secondMatches);
        $secondCode = $secondMatches[1];
        $this->assertNotSame($firstCode, $secondCode);

        $this->from(route('phone-verification.notice'))
            ->post(route('phone-verification.verify'), ['code' => $firstCode])
            ->assertRedirect(route('phone-verification.notice'))
            ->assertSessionHasErrors('code');

        Carbon::setTestNow(now()->addMinutes(6));
        $this->from(route('phone-verification.notice'))
            ->post(route('phone-verification.verify'), ['code' => $secondCode])
            ->assertRedirect(route('phone-verification.notice'))
            ->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->phone_verified_at);
        Carbon::setTestNow();
    }

    public function test_wrong_code_attempts_are_limited_and_sms_delivery_failure_never_verifies_phone(): void
    {
        $gateway = new TestSmsGateway;
        $this->app->instance(SmsGateway::class, $gateway);
        $user = User::factory()->create(['phone_verified_at' => null]);
        $this->actingAs($user)->post(route('phone-verification.send'))->assertRedirect();

        foreach (range(1, 5) as $attempt) {
            $this->from(route('phone-verification.notice'))
                ->post(route('phone-verification.verify'), ['code' => '000000'])
                ->assertRedirect(route('phone-verification.notice'))
                ->assertSessionHasErrors('code');
        }

        $challenge = SmsVerificationChallenge::query()->sole();
        $this->assertSame(5, $challenge->attempts);
        $this->assertNull($challenge->code_hash);
        $this->assertNull($user->fresh()->phone_verified_at);

        $this->app->instance(SmsGateway::class, new FailingSmsGateway);
        Carbon::setTestNow(now()->addSeconds(61));
        $this->from(route('phone-verification.notice'))
            ->post(route('phone-verification.send'))
            ->assertRedirect(route('phone-verification.notice'))
            ->assertSessionHasErrors('phone');
        $this->assertNull($challenge->fresh()->code_hash);
        $this->assertNull($user->fresh()->phone_verified_at);
        Carbon::setTestNow();
    }

    public function test_sms_sends_are_limited_to_five_per_hour_for_an_account(): void
    {
        Carbon::setTestNow(now());
        $gateway = new TestSmsGateway;
        $this->app->instance(SmsGateway::class, $gateway);
        $user = User::factory()->create(['phone_verified_at' => null]);
        $this->actingAs($user);

        foreach (range(1, 5) as $send) {
            if ($send > 1) {
                Carbon::setTestNow(now()->addSeconds(61));
            }
            $this->post(route('phone-verification.send'))->assertRedirect();
        }

        Carbon::setTestNow(now()->addSeconds(61));
        $this->from(route('phone-verification.notice'))
            ->post(route('phone-verification.send'))
            ->assertRedirect(route('phone-verification.notice'))
            ->assertSessionHasErrors('phone');
        $this->assertSame(5, SmsVerificationChallenge::query()->sole()->sends_in_window);

        Carbon::setTestNow(now()->addHour());
        $this->post(route('phone-verification.send'))->assertRedirect();
        $this->assertSame(1, SmsVerificationChallenge::query()->sole()->sends_in_window);
        Carbon::setTestNow();
    }

    public function test_phone_change_resets_verification_and_invalidates_the_previous_challenge(): void
    {
        $gateway = new TestSmsGateway;
        $this->app->instance(SmsGateway::class, $gateway);
        $user = User::factory()->create(['phone_verified_at' => null]);
        $this->actingAs($user)->post(route('phone-verification.send'))->assertRedirect();
        $this->assertNotNull(SmsVerificationChallenge::query()->sole()->code_hash);

        $this->put(route('mypage.profile.update'), [
            'legal_name' => '会員本人',
            'name' => $user->name,
            'email' => $user->email,
            'prefecture' => '東京都',
            'birth_date' => '1990-01-01',
            'phone' => '+819076543210',
            'phone_verified_at' => now(),
        ])->assertRedirect();

        $this->assertNull($user->fresh()->phone_verified_at);
        $challenge = SmsVerificationChallenge::query()->sole();
        $this->assertNull($challenge->phone_hash);
        $this->assertNull($challenge->code_hash);
    }

    public function test_admin_access_requires_phone_verification_but_recovery_routes_remain_available(): void
    {
        $admin = User::factory()->administrator()->create(['phone_verified_at' => null]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('phone-verification.notice'));

        $this->get(route('mypage.profile'))->assertOk();
        $this->get(route('phone-verification.notice'))->assertOk();
    }

    public function test_only_local_admin_routes_allow_existing_admins_during_sms_provider_setup(): void
    {
        $admin = User::factory()->administrator()->create(['phone_verified_at' => null]);
        $this->actingAs($admin);

        $this->app->detectEnvironment(fn () => 'local');
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('mypage'))->assertRedirect(route('phone-verification.notice'));

        $this->app->detectEnvironment(fn () => 'production');
        $this->get(route('admin.dashboard'))->assertRedirect(route('phone-verification.notice'));
    }
}

class TestSmsGateway implements SmsGateway
{
    public string $message = '';

    public function send(string $phone, string $message): void
    {
        $this->message = $message;
    }
}

class FailingSmsGateway implements SmsGateway
{
    public function send(string $phone, string $message): void
    {
        throw new SmsDeliveryException('送信失敗');
    }
}
