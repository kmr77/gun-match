<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AnnouncementNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::query()->latest('updated_at')->paginate(20);

        return view('pages.admin-announcements', compact('announcements'));
    }

    public function create(): View
    {
        $announcement = new Announcement;

        return view('pages.admin-announcement-form', compact('announcement'));
    }

    public function store(Request $request, AnnouncementNotificationService $notificationService): RedirectResponse
    {
        $announcement = Announcement::create($this->validatedData($request));
        $notificationService->notifyEligibleMembers($announcement);

        return redirect()->route('admin.announcements.edit', $announcement)
            ->with('status', 'お知らせを作成しました。');
    }

    public function edit(Announcement $announcement): View
    {
        return view('pages.admin-announcement-form', compact('announcement'));
    }

    public function update(
        Request $request,
        Announcement $announcement,
        AnnouncementNotificationService $notificationService,
    ): RedirectResponse {
        $announcement->update($this->validatedData($request, $announcement));
        $notificationService->notifyEligibleMembers($announcement);

        return redirect()->route('admin.announcements.edit', $announcement)
            ->with('status', 'お知らせを更新しました。');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', 'お知らせを削除しました。');
    }

    /**
     * @return array{title: string, body: string, status: string, published_at: mixed}
     */
    private function validatedData(Request $request, ?Announcement $announcement = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:20000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ], [
            'title.required' => 'タイトルを入力してください。',
            'title.max' => 'タイトルは150文字以内で入力してください。',
            'body.required' => '本文を入力してください。',
            'body.max' => '本文は20,000文字以内で入力してください。',
            'status.in' => '有効な公開状態を選択してください。',
            'published_at.date' => '有効な公開日時を入力してください。',
        ]);

        if ($validated['status'] === 'published') {
            $validated['published_at'] ??= $announcement?->published_at ?? now();
        }

        return $validated;
    }
}
