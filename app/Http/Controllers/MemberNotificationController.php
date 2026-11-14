<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\MemberNotification;
use App\Services\AnnouncementNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberNotificationController extends Controller
{
    public function index(Request $request, AnnouncementNotificationService $notificationService): View
    {
        $user = $request->user();
        $notificationService->syncPublishedAnnouncementsFor($user);

        $notifications = $user->memberNotifications()
            ->with('announcement')
            ->whereHas('announcement', fn ($query) => $query->published())
            ->latest('created_at')
            ->paginate(10);

        $unreadNotificationCount = $user->memberNotifications()
            ->unread()
            ->whereHas('announcement', fn ($query) => $query->published())
            ->count();

        return view('pages.mypage', [
            'pageKey' => 'notifications',
            'title' => 'サイト内通知',
            'notifications' => $notifications,
            'unreadNotificationCount' => $unreadNotificationCount,
        ]);
    }

    public function markRead(Request $request, MemberNotification $notification): RedirectResponse
    {
        $notification = $request->user()->memberNotifications()
            ->whereKey($notification->getKey())
            ->whereHas('announcement', fn ($query) => $query->published())
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return redirect()->route('mypage.notifications')->with('status', '通知を既読にしました。');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $publishedAnnouncementIds = Announcement::query()->published()->select('id');

        $request->user()->memberNotifications()
            ->unread()
            ->whereIn('announcement_id', $publishedAnnouncementIds)
            ->update(['read_at' => now()]);

        return redirect()->route('mypage.notifications')->with('status', '未読通知をすべて既読にしました。');
    }
}
