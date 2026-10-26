<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StaticPageController extends Controller
{
    public function information(string $page): View
    {
        $content = config("gunmatch_pages.information.{$page}");
        abort_if($content === null, 404);

        return view('pages.information', [
            'page' => $content,
            'pageKey' => $page,
        ]);
    }

    public function authPreview(string $page): View
    {
        $content = config("gunmatch_pages.auth.{$page}");
        abort_if($content === null, 404);

        return view('pages.auth-preview', [
            'page' => $content,
            'pageKey' => $page,
        ]);
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
            'notifications' => '通知設定',
            'announcements' => 'お知らせ一覧',
        ];

        abort_unless(array_key_exists($page, $titles), 404);

        return view('pages.mypage', [
            'pageKey' => $page,
            'title' => $titles[$page],
        ]);
    }
}
