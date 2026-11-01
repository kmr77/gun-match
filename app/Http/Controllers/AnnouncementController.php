<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Contracts\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = $this->publishedAnnouncements()->paginate(20);

        return view('pages.announcements', compact('announcements'));
    }

    public function show(Announcement $announcement): View
    {
        abort_unless(
            $announcement->status === 'published'
                && $announcement->published_at !== null
                && $announcement->published_at->lte(now()),
            404,
        );

        return view('pages.announcement', compact('announcement'));
    }

    private function publishedAnnouncements()
    {
        return Announcement::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}
