<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    public function test_all_public_and_member_pages_have_named_routes_and_render(): void
    {
        $pages = [
            'about' => '/about',
            'guide' => '/guide',
            'safety' => '/safety',
            'prohibited-items' => '/prohibited-items',
            'faq' => '/faq',
            'terms' => '/terms',
            'privacy' => '/privacy',
            'contact' => '/contact',
            'login' => '/login',
            'register' => '/register',
            'password.request' => '/forgot-password',
            'mypage' => '/mypage',
            'mypage.profile' => '/mypage/profile',
            'mypage.profile.public' => '/mypage/profile/public',
            'mypage.profile.preview' => '/mypage/profile/preview',
            'mypage.settings' => '/mypage/settings',
            'mypage.security' => '/mypage/security',
            'mypage.account' => '/mypage/account',
            'mypage.notifications' => '/mypage/notifications',
            'mypage.announcements' => '/mypage/announcements',
        ];

        foreach ($pages as $name => $uri) {
            $this->assertTrue(Route::has($name), "Missing named route: {$name}");
            $this->assertSame($uri, parse_url(route($name), PHP_URL_PATH));
            $this->get(route($name))->assertOk();
        }
    }

    public function test_static_pages_link_only_to_registered_local_routes(): void
    {
        $pageRoutes = [
            'about', 'guide', 'safety', 'prohibited-items', 'faq', 'terms', 'privacy', 'contact',
            'login', 'register', 'password.request', 'mypage', 'mypage.profile',
            'mypage.profile.public', 'mypage.profile.preview', 'mypage.settings',
            'mypage.security', 'mypage.account', 'mypage.notifications', 'mypage.announcements',
        ];
        $knownPaths = array_fill_keys(
            array_map(fn (string $name): string => parse_url(route($name), PHP_URL_PATH) ?: '/', ['home', ...$pageRoutes]),
            true,
        );

        foreach (['home', ...$pageRoutes] as $name) {
            $html = $this->get(route($name))->assertOk()->getContent();
            preg_match_all('/href="([^"]+)"/', $html, $matches);

            foreach ($matches[1] as $href) {
                if (str_starts_with($href, '#')) {
                    continue;
                }

                $parts = parse_url($href);
                if (isset($parts['host']) && $parts['host'] !== parse_url(route('home'), PHP_URL_HOST)) {
                    continue;
                }

                if (! isset($parts['host']) && ! str_starts_with($href, '/')) {
                    continue;
                }

                $path = $parts['path'] ?? '/';
                if (str_starts_with($path, '/assets/')) {
                    continue;
                }

                $this->assertArrayHasKey($path, $knownPaths, "Unregistered local link {$href} on {$name}");
            }
        }
    }

    public function test_member_previews_do_not_submit_or_claim_to_process_data(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('会員認証は未実装です')
            ->assertSee('type="button"', false)
            ->assertDontSee('method="POST"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('会員登録・認証・データ保存は未実装です');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('認証とメール送信は未実装です');

        $this->get(route('mypage'))
            ->assertOk()
            ->assertSee('実際の会員情報や通知設定は表示していません');

        $this->get(route('mypage.profile.public'))
            ->assertOk()
            ->assertSee('表示名のサンプル')
            ->assertSee('実在の利用者や個人の情報は含まれていません');

        $this->get(route('mypage.profile.preview'))
            ->assertOk()
            ->assertSee('プロフィールは保存・公開されていません');

        $this->get(route('mypage.security'))
            ->assertOk()
            ->assertSee('セキュリティ機能は未実装です');

        $this->get(route('mypage.account'))
            ->assertOk()
            ->assertSee('アカウント管理は未実装です');

        $this->get(route('mypage.announcements'))
            ->assertOk()
            ->assertSee('実際のお知らせや配信履歴ではありません')
            ->assertSee('お知らせ機能は未実装です');
    }

    public function test_unknown_page_uses_custom_not_found_page(): void
    {
        $this->get('/not-a-real-gunmatch-page')
            ->assertNotFound()
            ->assertSee('ページが見つかりません')
            ->assertSee(route('home'));
    }
}
