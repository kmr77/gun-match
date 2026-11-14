<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\MemberNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_announcements_create_deduplicated_member_notifications(): void
    {
        $admin = User::factory()->administrator()->create();
        $member = User::factory()->create();
        $shop = User::factory()->create(['member_type' => 'business']);

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => '運営からのお知らせ',
            'body' => '公開されたお知らせです。',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect();

        $announcement = Announcement::query()->where('title', '運営からのお知らせ')->firstOrFail();
        $this->assertDatabaseHas('member_notifications', [
            'user_id' => $member->id,
            'announcement_id' => $announcement->id,
            'read_at' => null,
        ]);
        $this->assertDatabaseHas('member_notifications', [
            'user_id' => $shop->id,
            'announcement_id' => $announcement->id,
        ]);

        $this->put(route('admin.announcements.update', $announcement), [
            'title' => '運営からのお知らせ',
            'body' => '更新されたお知らせです。',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect();

        $this->assertSame(1, MemberNotification::query()
            ->where('user_id', $member->id)
            ->where('announcement_id', $announcement->id)
            ->count());
    }

    public function test_members_can_read_and_mark_only_their_own_published_announcement_notifications(): void
    {
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $announcement = Announcement::create([
            'title' => '公開中のお知らせ',
            'body' => 'お知らせ本文です。',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        $draft = Announcement::create([
            'title' => '非公開のお知らせ',
            'body' => '公開前の内容です。',
            'status' => 'draft',
        ]);
        $notification = MemberNotification::create([
            'user_id' => $member->id,
            'announcement_id' => $announcement->id,
        ]);
        $otherNotification = MemberNotification::create([
            'user_id' => $otherMember->id,
            'announcement_id' => $announcement->id,
        ]);
        MemberNotification::create([
            'user_id' => $member->id,
            'announcement_id' => $draft->id,
        ]);

        $this->get(route('mypage.notifications'))
            ->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('mypage.notifications'))
            ->assertOk()
            ->assertSee('公開中のお知らせ')
            ->assertSee('未読1件')
            ->assertDontSee('非公開のお知らせ');

        $this->actingAs($member)->patch(route('mypage.notifications.read', $otherNotification))
            ->assertNotFound();
        $this->assertNull($otherNotification->fresh()->read_at);

        $this->actingAs($member)->patch(route('mypage.notifications.read', $notification))
            ->assertRedirect(route('mypage.notifications'));
        $this->assertNotNull($notification->fresh()->read_at);

        $unread = MemberNotification::create([
            'user_id' => $member->id,
            'announcement_id' => Announcement::create([
                'title' => '追加のお知らせ',
                'body' => '既読一括操作の確認用です。',
                'status' => 'published',
                'published_at' => now(),
            ])->id,
        ]);
        $this->actingAs($member)->patch(route('mypage.notifications.read-all'))
            ->assertRedirect(route('mypage.notifications'));
        $this->assertNotNull($unread->fresh()->read_at);
        $this->assertNull($otherNotification->fresh()->read_at);
    }

    public function test_notification_feed_syncs_published_announcements_without_duplicates(): void
    {
        $member = User::factory()->create();
        $announcement = Announcement::create([
            'title' => '通知一覧で同期するお知らせ',
            'body' => '公開済みのお知らせです。',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->actingAs($member)->get(route('mypage.notifications'))
            ->assertOk()
            ->assertSee('通知一覧で同期するお知らせ');
        $this->get(route('mypage.notifications'))->assertOk();

        $this->assertSame(1, MemberNotification::query()
            ->where('user_id', $member->id)
            ->where('announcement_id', $announcement->id)
            ->count());
    }

    public function test_notification_pages_require_email_and_phone_verification(): void
    {
        $unverifiedEmail = User::factory()->unverified()->create();
        $unverifiedPhone = User::factory()->create(['phone_verified_at' => null]);

        $this->actingAs($unverifiedEmail)->get(route('mypage.notifications'))
            ->assertRedirect(route('verification.notice'));
        $this->actingAs($unverifiedPhone)->get(route('mypage.notifications'))
            ->assertRedirect(route('phone-verification.notice'));
    }
}
