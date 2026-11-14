<?php

namespace App\Console\Commands;

use App\Services\Sms\SmsGateway;
use App\Services\Sms\UnconfiguredSmsGateway;
use App\Support\LegalTerms;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDOException;

class ProductionReadinessCheck extends Command
{
    protected $signature = 'gunmatch:production-check';

    protected $description = '本番運用設定を秘密情報を表示せず診断します';

    private bool $ready = true;

    public function handle(): int
    {
        $this->check('PHP 8.3以上', PHP_VERSION_ID >= 80300);
        $this->check('アプリケーションキー設定', is_string(config('app.key')) && trim(config('app.key')) !== '');
        $this->check('デバッグ無効', config('app.debug') === false);
        $this->check('HTTPSのAPP_URL', parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https');
        $this->check('セッションCookieのSecure属性', config('session.secure') === true);
        $this->checkDatabase();
        $this->checkMail();
        $this->check(
            'SMS事業者アダプター',
            ! (app(SmsGateway::class) instanceof UnconfiguredSmsGateway),
        );
        $this->check('正式な利用規約', LegalTerms::publishedDocument('terms') !== null);
        $this->check('正式なプライバシーポリシー', LegalTerms::publishedDocument('privacy') !== null);
        $this->warn('個人情報の保存期間・退会後の保持方針は運営・法務確認が別途必要です。');

        if ($this->ready) {
            $this->info('設定診断上の必須項目は確認できました。外部サービスの実配送・本番動作は別途検証してください。');

            return self::SUCCESS;
        }

        $this->error('未完了項目があります。本番公開前に設定・運営判断を完了してください。');

        return self::FAILURE;
    }

    private function checkDatabase(): void
    {
        $mysqlSelected = config('database.default') === 'mysql';
        if (! $mysqlSelected) {
            $this->check('MySQL 8接続', false);

            return;
        }

        try {
            $version = DB::connection()->selectOne('SELECT VERSION() AS version')?->version;
            $this->check(
                'MySQL 8接続',
                is_string($version) && preg_match('/\A8\./', $version) === 1,
            );
        } catch (PDOException) {
            $this->check('MySQL 8接続', false);
        }
    }

    private function checkMail(): void
    {
        $smtp = config('mail.mailers.smtp', []);
        $host = $smtp['host'] ?? null;
        $username = $smtp['username'] ?? null;
        $password = $smtp['password'] ?? null;
        $sender = config('mail.from.address');
        $port = filter_var($smtp['port'] ?? null, FILTER_VALIDATE_INT);
        $scheme = $smtp['scheme'] ?? null;

        $valid = config('mail.default') === 'smtp'
            && is_string($host)
            && trim($host) !== ''
            && ! in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)
            && $port !== false
            && $port > 0
            && is_string($username)
            && filter_var($username, FILTER_VALIDATE_EMAIL)
            && is_string($password)
            && $password !== ''
            && in_array($scheme, ['smtp', 'smtps'], true)
            && ($scheme === 'smtps' || ($smtp['require_tls'] ?? false) === true)
            && is_string($sender)
            && filter_var($sender, FILTER_VALIDATE_EMAIL)
            && ! str_ends_with(strtolower($sender), '@example.com');

        $this->check('認証付きTLS SMTP', $valid);
    }

    private function check(string $label, bool $passed): void
    {
        $this->line(sprintf('%s %s', $passed ? '<info>OK</info>' : '<error>要対応</error>', $label));
        $this->ready = $this->ready && $passed;
    }
}
