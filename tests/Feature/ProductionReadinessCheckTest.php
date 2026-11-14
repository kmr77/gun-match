<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionReadinessCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_diagnostic_reports_statuses_without_revealing_configuration_values(): void
    {
        config([
            'app.key' => 'SECRET_APP_KEY_SENTINEL',
            'app.debug' => false,
            'app.url' => 'https://gunmatch.example.test',
            'session.secure' => true,
            'database.default' => 'mysql',
            'mail.default' => 'smtp',
            'mail.from.address' => 'sender-secret@example.test',
            'mail.mailers.smtp' => [
                'host' => 'smtp-secret.example.test',
                'port' => 465,
                'scheme' => 'smtps',
                'username' => 'smtp-user-secret@example.test',
                'password' => 'SECRET_SMTP_PASSWORD_SENTINEL',
                'require_tls' => false,
            ],
        ]);

        $exitCode = Artisan::call('gunmatch:production-check');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('MySQL 8接続', $output);
        $this->assertStringContainsString('認証付きTLS SMTP', $output);
        $this->assertStringContainsString('SMS事業者アダプター', $output);
        $this->assertStringContainsString('正式な利用規約', $output);
        $this->assertStringContainsString('保存期間', $output);
        $this->assertStringNotContainsString('SECRET_APP_KEY_SENTINEL', $output);
        $this->assertStringNotContainsString('SECRET_SMTP_PASSWORD_SENTINEL', $output);
        $this->assertStringNotContainsString('smtp-secret.example.test', $output);
        $this->assertStringNotContainsString('smtp-user-secret@example.test', $output);
        $this->assertStringNotContainsString('sender-secret@example.test', $output);
    }
}
