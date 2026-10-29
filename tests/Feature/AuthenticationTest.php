<?php

namespace Tests\Feature;

use App\Enums\MemberType;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_unverified_member_and_starts_a_session(): void
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'member_type' => MemberType::Individual->value,
            'name' => 'テスト会員',
            'email' => 'member@example.test',
            'password' => 'GunMatch-Test!234',
            'password_confirmation' => 'GunMatch-Test!234',
        ]);

        $user = User::where('email', 'member@example.test')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);
        $this->assertSame(MemberType::Individual, $user->member_type);
        $this->assertTrue(Hash::check('GunMatch-Test!234', $user->password));
        $this->assertNotSame('GunMatch-Test!234', $user->password);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_supports_the_gun_shop_member_type(): void
    {
        $this->post(route('register'), [
            'member_type' => MemberType::GunShop->value,
            'name' => 'テスト銃砲店',
            'email' => 'shop@example.test',
            'password' => 'GunMatch-Shop!234',
            'password_confirmation' => 'GunMatch-Shop!234',
        ])->assertRedirect(route('verification.notice'));

        $this->assertSame(
            MemberType::GunShop,
            User::where('email', 'shop@example.test')->firstOrFail()->member_type,
        );
    }

    public function test_registration_rejects_duplicate_email_invalid_member_type_and_weak_password(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $this->from(route('register'))->post(route('register'), [
            'member_type' => 'unknown',
            'name' => 'New Member',
            'email' => 'EXISTING@example.test',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['member_type', 'email', 'password']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_regenerates_session_and_logout_invalidates_it(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.test',
            'password' => 'GunMatch-Login!234',
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'GunMatch-Login!234',
            'remember' => '1',
        ])->assertRedirect(route('mypage'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('mypage'))->assertOk()->assertSee($user->email);

        $this->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHasNoErrors();

        $this->assertGuest();
        $this->get(route('mypage'))->assertRedirect(route('login'));
    }

    public function test_login_rejects_invalid_password_and_limits_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'limited@example.test',
            'password' => 'GunMatch-Login!234',
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))->post(route('login'), [
                'email' => $user->email,
                'password' => 'incorrect',
            ])->assertRedirect(route('login'))->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'incorrect',
        ])->assertStatus(429);
    }

    public function test_member_pages_require_authentication_and_profile_updates_are_scoped_to_the_current_user(): void
    {
        $this->get(route('mypage'))->assertRedirect(route('login'));
        $this->put(route('mypage.profile.update'), [
            'name' => 'Unauthorized',
            'email' => 'unauthorized@example.test',
        ])->assertRedirect(route('login'));

        $member = User::factory()->create(['name' => '本人', 'email' => 'owner@example.test']);
        $other = User::factory()->create(['name' => '別会員', 'email' => 'other@example.test']);

        $this->actingAs($member)->put(route('mypage.profile.update'), [
            'name' => '本人の更新後',
            'email' => 'owner@example.test',
            'user_id' => $other->id,
            'is_admin' => true,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $member->id, 'name' => '本人の更新後']);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'name' => '別会員', 'email' => 'other@example.test']);
        $this->assertFalse($member->fresh()->is_admin);
        $this->put('/mypage/profile/'.$other->id, [
            'name' => '改ざん',
            'email' => 'other@example.test',
        ])->assertNotFound();
    }

    public function test_profile_validation_rejects_duplicate_email_and_invalid_values(): void
    {
        $member = User::factory()->create(['email' => 'member@example.test']);
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($member)->from(route('mypage.profile'))->put(route('mypage.profile.update'), [
            'name' => '',
            'email' => 'TAKEN@example.test',
        ])->assertRedirect(route('mypage.profile'))
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertSame('member@example.test', $member->fresh()->email);
    }

    public function test_changing_email_resets_verification_and_sends_a_new_verification_notification(): void
    {
        Notification::fake();
        $member = User::factory()->create(['email' => 'verified@example.test']);

        $this->actingAs($member)->put(route('mypage.profile.update'), [
            'name' => $member->name,
            'email' => 'new-address@example.test',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertNull($member->fresh()->email_verified_at);
        Notification::assertSentTo($member->fresh(), VerifyEmail::class);
    }

    public function test_email_unverified_members_can_access_basic_account_pages_but_admin_pages_require_verification(): void
    {
        $member = User::factory()->unverified()->create();

        $this->actingAs($member)->get(route('mypage'))->assertOk();
        $this->get(route('mypage.profile'))->assertOk();

        $member->forceFill(['is_admin' => true])->save();
        $this->get(route('admin.dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_admin_access_is_separate_from_member_type_and_requires_admin_authorization(): void
    {
        $member = User::factory()->create(['member_type' => MemberType::GunShop]);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('管理者ページ');

        $this->actingAs($admin)->post(route('logout'));
        $this->post(route('register'), [
            'member_type' => MemberType::Individual->value,
            'name' => '一般会員',
            'email' => 'ordinary@example.test',
            'password' => 'GunMatch-Test!234',
            'password_confirmation' => 'GunMatch-Test!234',
            'is_admin' => true,
        ])->assertRedirect(route('verification.notice'));

        $ordinary = User::where('email', 'ordinary@example.test')->firstOrFail();
        $this->assertFalse($ordinary->is_admin);
        $this->assertArrayNotHasKey('is_admin', $ordinary->toArray());
    }

    public function test_account_deletion_requires_current_password_and_removes_account_sessions_and_reset_token(): void
    {
        $member = User::factory()->create([
            'email' => 'delete-me@example.test',
            'password' => 'GunMatch-Delete!234',
        ]);
        $other = User::factory()->create(['email' => 'keep-me@example.test']);
        DB::table('sessions')->insert([
            'id' => 'another-device-session',
            'user_id' => $member->id,
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
        DB::table('password_reset_tokens')->insert([
            'email' => $member->email,
            'token' => 'stored-token',
            'created_at' => now(),
        ]);

        $this->actingAs($member)->from(route('mypage.account'))->delete(route('mypage.account.destroy'), [
            'password' => 'incorrect',
        ])->assertRedirect(route('mypage.account'))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $member->id]);

        $this->delete(route('mypage.account.destroy'), [
            'password' => 'GunMatch-Delete!234',
            'user_id' => $other->id,
        ])->assertRedirect(route('home'))
            ->assertSessionHas('status', 'アカウントを削除しました。');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $member->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $member->email]);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'email' => 'keep-me@example.test']);
    }

    public function test_authenticated_member_can_change_password_after_current_password_check(): void
    {
        $member = User::factory()->create(['password' => 'GunMatch-Old!234']);

        $this->actingAs($member)->from(route('mypage.security'))->put(route('mypage.password.update'), [
            'current_password' => 'incorrect',
            'password' => 'GunMatch-New!234',
            'password_confirmation' => 'GunMatch-New!234',
        ])->assertRedirect(route('mypage.security'))
            ->assertSessionHasErrors('current_password');

        $this->put(route('mypage.password.update'), [
            'current_password' => 'GunMatch-Old!234',
            'password' => 'GunMatch-New!234',
            'password_confirmation' => 'GunMatch-New!234',
        ])->assertRedirect(route('mypage.security'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('GunMatch-New!234', $member->fresh()->password));
    }

    public function test_password_can_be_reset_with_a_valid_one_time_token(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.test']);

        $this->from(route('password.request'))->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.request'))->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });
        $this->assertNotNull($token);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('パスワード再設定');

        $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'GunMatch-Reset!234',
                'password_confirmation' => 'GunMatch-Reset!234',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('GunMatch-Reset!234', $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_invalid_or_expired_password_reset_tokens_are_rejected(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.test']);

        $this->from(route('password.reset', ['token' => 'invalid-token', 'email' => $user->email]))
            ->post(route('password.update'), [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'GunMatch-Reset!234',
                'password_confirmation' => 'GunMatch-Reset!234',
            ])
            ->assertRedirect(route('password.reset', ['token' => 'invalid-token', 'email' => $user->email]))
            ->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('GunMatch-Reset!234', $user->fresh()->password));
    }

    public function test_email_verification_uses_signed_links_and_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.notice'))->assertOk();
        $this->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('status');
        Notification::assertSentTo($user, VerifyEmail::class);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->get($url)->assertRedirect(route('mypage'))->assertSessionHas('status');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
