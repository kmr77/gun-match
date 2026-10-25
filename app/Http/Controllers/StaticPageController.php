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
        abort_unless(in_array($page, ['home', 'profile', 'settings', 'notifications'], true), 404);

        $titles = [
            'home' => 'マイページ',
            'profile' => 'プロフィール編集',
            'settings' => 'アカウント設定',
            'notifications' => '通知設定',
        ];

        return view('pages.mypage', [
            'pageKey' => $page,
            'title' => $titles[$page],
        ]);
    }
}
