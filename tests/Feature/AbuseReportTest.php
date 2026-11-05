<?php

namespace Tests\Feature;

use App\Models\AbuseReport;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbuseReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_submit_a_private_report(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('mypage.reports.create'))
            ->assertOk()
            ->assertSee('通報内容は運営のみが確認')
            ->assertSee('name="_token"', false);

        $this->actingAs($member)->post(route('mypage.reports.store'), [
            'type' => 'terms_violation',
            'target_information' => 'https://example.test/member/123',
            'details' => '利用規約に反する内容の確認をお願いします。',
        ])->assertRedirect(route('mypage.reports.create'))
            ->assertSessionHas('status');

        $report = AbuseReport::query()->firstOrFail();
        $this->assertSame($member->id, $report->reporter_user_id);
        $this->assertSame('pending', $report->status);
        $this->assertNull($report->handled_at);
        $this->assertDatabaseHas('abuse_reports', [
            'id' => $report->id,
            'type' => 'terms_violation',
            'target_information' => 'https://example.test/member/123',
        ]);

        $this->actingAs($member)->get(route('mypage'))
            ->assertOk()
            ->assertDontSee('利用規約に反する内容の確認をお願いします。')
            ->assertDontSee('https://example.test/member/123');
    }

    public function test_report_form_requires_authentication_and_validates_input(): void
    {
        $this->get(route('mypage.reports.create'))->assertRedirect(route('login'));
        $this->post(route('mypage.reports.store'), [])->assertRedirect(route('login'));

        $member = User::factory()->create();
        $this->actingAs($member)->from(route('mypage.reports.create'))
            ->post(route('mypage.reports.store'), [
                'type' => 'invalid',
                'target_information' => '',
                'details' => '',
            ])
            ->assertRedirect(route('mypage.reports.create'))
            ->assertSessionHasErrors(['type', 'target_information', 'details']);

        $this->assertDatabaseCount('abuse_reports', 0);
    }

    public function test_member_report_submission_is_rate_limited(): void
    {
        $member = User::factory()->create();
        $payload = [
            'type' => 'other',
            'target_information' => '対象のページ',
            'details' => '確認をお願いします。',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->actingAs($member)->post(route('mypage.reports.store'), $payload)
                ->assertRedirect(route('mypage.reports.create'));
        }

        $this->actingAs($member)->post(route('mypage.reports.store'), $payload)
            ->assertTooManyRequests();

        $this->assertDatabaseCount('abuse_reports', 5);
    }

    public function test_only_verified_admins_can_view_reports_and_record_responses(): void
    {
        $reporter = User::factory()->create(['name' => '通報者会員']);
        $otherMember = User::factory()->create();
        $admin = User::factory()->administrator()->create();
        $unverifiedAdmin = User::factory()->administrator()->unverified()->create();
        $report = AbuseReport::create([
            'reporter_user_id' => $reporter->id,
            'type' => 'safety_concern',
            'target_information' => '確認対象ページ',
            'details' => '管理者だけが閲覧できる通報詳細です。',
        ]);

        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
        $this->actingAs($otherMember)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($otherMember)->get(route('admin.reports.show', $report))->assertForbidden();
        $this->actingAs($otherMember)->patch(route('admin.reports.update', $report), [
            'status' => 'completed',
            'admin_note' => '権限のない変更',
        ])->assertForbidden();
        $this->assertSame('pending', $report->fresh()->status);
        $this->assertNull($report->fresh()->admin_note);
        $this->actingAs($unverifiedAdmin)->get(route('admin.reports.index'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('通報者会員')
            ->assertSee('安全上の懸念')
            ->assertSee(route('admin.members.show', $reporter));
        $this->get(route('admin.dashboard'))->assertSee(route('admin.reports.index'));
        $this->get(route('admin.reports.show', $report))
            ->assertOk()
            ->assertSee('管理者だけが閲覧できる通報詳細です。')
            ->assertSee($reporter->email)
            ->assertSee('管理者用対応メモ')
            ->assertDontSee('別会員の通報詳細');

        $this->patch(route('admin.reports.update', $report), [
            'status' => 'in_progress',
            'admin_note' => '内容を確認中です。',
        ])->assertRedirect(route('admin.reports.show', $report));

        $report->refresh();
        $this->assertSame('in_progress', $report->status);
        $this->assertSame('内容を確認中です。', $report->admin_note);
        $this->assertSame($admin->id, $report->handled_by_user_id);
        $this->assertNotNull($report->handled_at);
        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $reporter->id,
            'action' => 'report.updated',
        ]);
        $activity = AdminActivityLog::query()->where('actor_user_id', $admin->id)->latest('id')->firstOrFail();
        $this->assertSame($report->id, $activity->details['report_id']);
        $this->assertSame('pending', $activity->details['from_status']);
        $this->assertSame('in_progress', $activity->details['to_status']);
        $this->assertTrue($activity->details['admin_note_changed']);

        $this->patch(route('admin.reports.update', $report), [
            'status' => 'completed',
            'admin_note' => '対応を完了しました。',
        ])->assertRedirect(route('admin.reports.show', $report));
        $this->assertSame('completed', $report->fresh()->status);
        $this->assertSame(2, AdminActivityLog::query()->where('action', 'report.updated')->count());
    }

    public function test_admin_report_pages_remain_available_after_reporter_account_deletion(): void
    {
        $reporter = User::factory()->create([
            'email' => 'former-member@example.test',
            'password' => 'GunMatch-Former!234',
        ]);
        $report = AbuseReport::create([
            'reporter_user_id' => $reporter->id,
            'type' => 'other',
            'target_information' => '削除後も確認する対象',
            'details' => '通報本文の参照整合性確認',
        ]);
        $reportId = $report->id;

        $this->actingAs($reporter)->delete(route('mypage.account.destroy'), [
            'password' => 'GunMatch-Former!234',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('abuse_reports', [
            'id' => $reportId,
            'reporter_user_id' => null,
        ]);

        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('削除済み会員')
            ->assertSee(route('admin.reports.show', $reportId));

        $this->get(route('admin.reports.show', $reportId))
            ->assertOk()
            ->assertSee('通報本文の参照整合性確認')
            ->assertSee('削除済み会員')
            ->assertDontSee('former-member@example.test');
    }
}
