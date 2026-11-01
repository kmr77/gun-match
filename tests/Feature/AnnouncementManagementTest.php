<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_publish_edit_and_unpublish_an_announcement(): void
    {
        $admin = User::factory()->administrator()->create();

        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'GunMatchの運営案内',
            'body' => '公開するお知らせ本文です。',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ]);
        $announcement = Announcement::query()->firstOrFail();

        $response->assertRedirect(route('admin.announcements.edit', $announcement));
        $this->assertSame('published', $announcement->status);

        $this->get(route('announcements.index'))->assertOk()
            ->assertSee('GunMatchの運営案内')
            ->assertSee(route('announcements.show', $announcement));
        $this->get(route('announcements.show', $announcement))->assertOk()
            ->assertSee('公開するお知らせ本文です。');

        $this->put(route('admin.announcements.update', $announcement), [
            'title' => '更新後のお知らせ',
            'body' => '更新後の本文です。',
            'status' => 'draft',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('admin.announcements.edit', $announcement));

        $this->assertSame('draft', $announcement->fresh()->status);
        $this->get(route('announcements.index'))->assertDontSee('更新後のお知らせ');
        $this->get(route('announcements.show', $announcement))->assertNotFound();

        $this->put(route('admin.announcements.update', $announcement), [
            'title' => '更新後のお知らせ',
            'body' => '更新後の本文です。',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('admin.announcements.edit', $announcement));

        $this->get(route('announcements.show', $announcement))->assertOk()
            ->assertSee('更新後のお知らせ')
            ->assertSee('更新後の本文です。');
    }

    public function test_drafts_and_future_announcements_are_hidden_from_visitors_and_mypage(): void
    {
        $draft = Announcement::create([
            'title' => '非公開の下書き',
            'body' => '公開していない本文です。',
            'status' => 'draft',
        ]);
        $future = Announcement::create([
            'title' => '公開予定のお知らせ',
            'body' => '指定日時までは見えません。',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        $public = Announcement::create([
            'title' => '公開中のお知らせ',
            'body' => '利用者向けの公開本文です。',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        $olderPublic = Announcement::create([
            'title' => '以前のお知らせ',
            'body' => '新しいお知らせより前に公開されました。',
            'status' => 'published',
            'published_at' => now()->subMinutes(10),
        ]);

        $this->get(route('announcements.index'))->assertOk()
            ->assertSee('公開中のお知らせ')
            ->assertDontSee('非公開の下書き')
            ->assertDontSee('公開予定のお知らせ')
            ->assertSeeInOrder(['公開中のお知らせ', '以前のお知らせ']);
        $this->get(route('announcements.show', $draft))->assertNotFound();
        $this->get(route('announcements.show', $future))->assertNotFound();

        $member = User::factory()->create();
        $this->actingAs($member)->get(route('mypage.announcements'))->assertOk()
            ->assertSee('公開中のお知らせ')
            ->assertDontSee('非公開の下書き')
            ->assertDontSee('公開予定のお知らせ')
            ->assertSee(route('announcements.show', $public))
            ->assertSee('以前のお知らせ');
        $this->assertDatabaseHas('announcements', ['id' => $olderPublic->id]);
    }

    public function test_only_verified_admins_can_manage_announcements(): void
    {
        $announcement = Announcement::create([
            'title' => '管理者専用',
            'body' => '管理者だけが編集できます。',
            'status' => 'draft',
        ]);
        $member = User::factory()->create();
        $unverifiedAdmin = User::factory()->administrator()->unverified()->create();

        $this->get(route('admin.announcements.index'))->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.announcements.index'))->assertForbidden();
        $this->actingAs($unverifiedAdmin)->get(route('admin.announcements.index'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($member)->put(route('admin.announcements.update', $announcement), [
            'title' => '不正な更新',
            'body' => '拒否される更新です。',
            'status' => 'published',
        ])->assertForbidden();
        $this->assertSame('管理者専用', $announcement->fresh()->title);

        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)->delete(route('admin.announcements.destroy', $announcement))
            ->assertRedirect(route('admin.announcements.index'));
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }
}
