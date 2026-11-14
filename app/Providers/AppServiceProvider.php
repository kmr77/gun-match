<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\UnconfiguredSmsGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, UnconfiguredSmsGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->assertProductionMailIsConfigured();

        Gate::define('access-admin', fn (User $user): bool => (bool) $user->is_admin);

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::transliterate(Str::lower((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }

    private function assertProductionMailIsConfigured(): void
    {
        if (! $this->app->environment('production')) {
            return;
        }

        if (
            $this->app->runningInConsole()
            && in_array('gunmatch:production-check', $_SERVER['argv'] ?? [], true)
        ) {
            return;
        }

        $smtp = config('mail.mailers.smtp', []);
        $host = $smtp['host'] ?? null;
        $port = filter_var($smtp['port'] ?? null, FILTER_VALIDATE_INT);
        $scheme = $smtp['scheme'] ?? null;
        $username = $smtp['username'] ?? null;
        $sender = config('mail.from.address');

        $valid = config('mail.default') === 'smtp'
            && is_string($host)
            && trim($host) !== ''
            && ! in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)
            && $port !== false
            && $port > 0
            && is_string($username)
            && filter_var($username, FILTER_VALIDATE_EMAIL)
            && is_string($smtp['password'] ?? null)
            && $smtp['password'] !== ''
            && in_array($scheme, ['smtp', 'smtps'], true)
            && ($scheme === 'smtps' || ($smtp['require_tls'] ?? false) === true)
            && is_string($sender)
            && filter_var($sender, FILTER_VALIDATE_EMAIL)
            && ! str_ends_with(strtolower($sender), '@example.com');

        if (! $valid) {
            throw new RuntimeException('本番メール設定が未完了です。認証付きSMTPとTLS設定を確認してください。');
        }
    }
}
