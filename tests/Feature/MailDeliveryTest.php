<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Tests\TestCase;

class MailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_mail_defaults_to_memory_transport_without_log_or_fallback_mailers(): void
    {
        $this->assertSame('array', config('mail.default'));
        $this->assertSame('array', config('mail.mailers.log.transport'));
        $this->assertNotContains('log', config('mail.mailers.failover.mailers'));
    }

    public function test_production_rejects_log_array_or_insecure_smtp_configuration(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        $configurations = [
            ['mail.default' => 'array'],
            [
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    'host' => 'smtp.example.test',
                    'port' => 587,
                    'scheme' => 'smtp',
                    'username' => 'mailer@gunmatch.test',
                    'password' => 'test-only',
                    'require_tls' => false,
                ],
            ],
            [
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    'host' => null,
                    'port' => 587,
                    'scheme' => 'smtp',
                    'username' => null,
                    'password' => null,
                    'require_tls' => true,
                ],
            ],
        ];

        foreach ($configurations as $configuration) {
            foreach ($configuration as $key => $value) {
                $this->app['config']->set($key, $value);
            }

            try {
                (new AppServiceProvider($this->app))->boot();
                $this->fail('Invalid production mail configuration was accepted.');
            } catch (RuntimeException $exception) {
                $this->assertSame(
                    '本番メール設定が未完了です。認証付きSMTPとTLS設定を確認してください。',
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_production_accepts_authenticated_smtp_with_required_tls_without_connecting(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        $this->app['config']->set('mail.default', 'smtp');
        $this->app['config']->set('mail.from.address', 'no-reply@gunmatch.test');
        $this->app['config']->set('mail.mailers.smtp', [
            'host' => 'smtp.example.test',
            'port' => 465,
            'scheme' => 'smtps',
            'username' => 'mailer@gunmatch.test',
            'password' => 'test-only',
            'require_tls' => false,
        ]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('smtp', config('mail.default'));
    }

    public function test_verification_mail_transport_failure_is_visible_without_verifying_the_account(): void
    {
        $user = User::factory()->unverified()->create();
        $this->useFailingMailTransport();
        Log::shouldReceive('warning')
            ->once()
            ->with('Authentication email delivery failed.', ['flow' => 'email_verification']);

        $this->actingAs($user)->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('mail_delivery_error')
            ->assertSessionMissing('status');

        $this->assertNull($user->fresh()->email_verified_at);
        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('確認メールを送信できませんでした');
    }

    public function test_password_reset_mail_failure_is_reported_without_leaving_an_unusable_token(): void
    {
        $user = User::factory()->create();
        $this->useFailingMailTransport();
        Log::shouldReceive('warning')
            ->once()
            ->with('Authentication email delivery failed.', ['flow' => 'password_reset']);

        $this->from(route('password.request'))->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.request'))
            ->assertSessionHas('mail_delivery_error')
            ->assertSessionMissing('status');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_registration_mail_failure_keeps_the_account_unverified_and_shows_an_error(): void
    {
        $this->useFailingMailTransport();
        Log::shouldReceive('warning')
            ->once()
            ->with('Authentication email delivery failed.', ['flow' => 'registration_verification']);

        $this->post(route('register'), [
            'member_type' => 'individual',
            'legal_name' => 'テスト本名',
            'name' => 'テスト会員',
            'email' => 'mail-failure@example.test',
            'phone' => '+819012345678',
            'prefecture' => '東京都',
            'birth_date' => now()->subYears(30)->toDateString(),
            'password' => 'GunMatch-Mail!234',
            'password_confirmation' => 'GunMatch-Mail!234',
        ])->assertRedirect(route('verification.notice'))
            ->assertSessionHas('mail_delivery_error');

        $user = User::where('email', 'mail-failure@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->is_active);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_email_change_mail_failure_leaves_the_new_address_unverified(): void
    {
        $user = User::factory()->create(['email' => 'old-address@example.test']);
        $this->useFailingMailTransport();
        Log::shouldReceive('warning')
            ->once()
            ->with('Authentication email delivery failed.', ['flow' => 'profile_email_change']);

        $this->actingAs($user)->from(route('mypage.profile'))->put(route('mypage.profile.update'), [
            'legal_name' => '本人の本名',
            'name' => $user->name,
            'email' => 'new-address@example.test',
            'prefecture' => '東京都',
            'birth_date' => now()->subYears(30)->toDateString(),
            'phone' => $user->phone,
        ])->assertRedirect(route('mypage.profile'))
            ->assertSessionHas('mail_delivery_error');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new-address@example.test',
            'email_verified_at' => null,
        ]);
    }

    private function useFailingMailTransport(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::extend('smtp', fn (): FailingMailTransport => new FailingMailTransport);
        Mail::purge('smtp');
    }
}

class FailingMailTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        throw new TransportException('test SMTP transport failure');
    }

    public function __toString(): string
    {
        return 'test-failing-smtp';
    }
}
