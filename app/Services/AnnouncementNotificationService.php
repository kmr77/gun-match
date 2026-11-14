<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnnouncementNotificationService
{
    public function syncPublishedAnnouncementsFor(User $user): void
    {
        $announcementIds = Announcement::query()->published()->pluck('id');

        if ($announcementIds->isEmpty()) {
            return;
        }

        $this->insertForUser($user, $announcementIds->all());
    }

    public function notifyEligibleMembers(Announcement $announcement): void
    {
        if (
            $announcement->status !== 'published'
            || $announcement->published_at === null
            || $announcement->published_at->isFuture()
        ) {
            return;
        }

        User::query()
            ->where('is_active', true)
            ->whereNotNull('email_verified_at')
            ->whereNotNull('phone_verified_at')
            ->select(['id'])
            ->chunkById(500, function ($users) use ($announcement): void {
                $now = now();
                $rows = $users->map(fn (User $user): array => [
                    'user_id' => $user->id,
                    'announcement_id' => $announcement->id,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('member_notifications')->insertOrIgnore($rows);
            });
    }

    /**
     * @param  array<int, int>  $announcementIds
     */
    private function insertForUser(User $user, array $announcementIds): void
    {
        $now = now();
        $rows = array_map(fn (int $announcementId): array => [
            'user_id' => $user->id,
            'announcement_id' => $announcementId,
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $announcementIds);

        DB::table('member_notifications')->insertOrIgnore($rows);
    }
}
