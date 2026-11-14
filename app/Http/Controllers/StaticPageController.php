<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Support\LegalTerms;
use Illuminate\Contracts\View\View;

class StaticPageController extends Controller
{
    public function information(string $page): View
    {
        $content = config("gunmatch_pages.information.{$page}");
        abort_if($content === null, 404);

        $legalDocument = in_array($page, ['terms', 'privacy'], true)
            ? LegalTerms::publishedDocument($page)
            : null;

        if ($legalDocument !== null) {
            $content['description'] = $page === 'terms'
                ? 'GunMatchの利用規約です。'
                : 'GunMatchのプライバシーポリシーです。';
            $content['notice'] = '文書版 '.$legalDocument['version'].'・発効日 '.$legalDocument['effective_at'];
            $content['sections'] = $legalDocument['sections'];
        }

        return view('pages.information', [
            'page' => $content,
            'pageKey' => $page,
        ]);
    }

    public function authPreview(string $page): View
    {
        $content = config("gunmatch_pages.auth.{$page}");
        abort_if($content === null, 404);

        $data = [
            'page' => $content,
            'pageKey' => $page,
        ];

        if ($page === 'register') {
            $data['prefectures'] = config('gunmatch_registration.prefectures');
            $data['termsConsentVersion'] = LegalTerms::registrationVersion();
        }

        return view('pages.auth-preview', $data);
    }

    public function mypage(string $page): View
    {
        $titles = [
            'home' => 'マイページ',
            'profile' => 'プロフィール編集',
            'profile-public' => '公開プロフィール',
            'profile-preview' => 'プロフィールプレビュー',
            'settings' => 'アカウント設定',
            'security' => 'セキュリティ設定',
            'account' => 'アカウント管理のご案内',
            'announcements' => 'お知らせ一覧',
        ];

        abort_unless(array_key_exists($page, $titles), 404);

        $user = auth()->user();
        $data = [
            'pageKey' => $page,
            'title' => $titles[$page],
            'unreadNotificationCount' => $user->memberNotifications()
                ->unread()
                ->whereHas('announcement', fn ($query) => $query->published())
                ->count(),
        ];

        if ($page === 'announcements') {
            $data['announcements'] = Announcement::query()
                ->published()
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(10);
        }

        return view('pages.mypage', $data);
    }
}
