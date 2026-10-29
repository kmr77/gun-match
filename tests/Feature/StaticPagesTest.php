<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_and_member_pages_have_named_routes_and_render(): void
    {
        $pages = [
            'categories.index' => '/categories',
            'outdoor-samples.index' => '/outdoor-samples',
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

        $memberRoutes = [
            'mypage',
            'mypage.profile',
            'mypage.profile.public',
            'mypage.profile.preview',
            'mypage.settings',
            'mypage.security',
            'mypage.account',
            'mypage.notifications',
            'mypage.announcements',
        ];

        foreach ($pages as $name => $uri) {
            $this->assertTrue(Route::has($name), "Missing named route: {$name}");
            $this->assertSame($uri, parse_url(route($name), PHP_URL_PATH));
            $request = in_array($name, $memberRoutes, true)
                ? $this->actingAs(User::factory()->create())->get(route($name))
                : $this->get(route($name));
            $request->assertOk();
        }
    }

    public function test_all_categories_use_the_shared_category_detail_template(): void
    {
        $categories = config('gunmatch_categories');
        $this->assertCount(7, $categories);

        foreach ($categories as $category) {
            $this->get(route('categories.show', ['slug' => $category['slug']]))
                ->assertOk()
                ->assertViewIs('pages.category')
                ->assertViewHas('category', $category)
                ->assertSee($category['name'])
                ->assertSee('assets/img/categories/'.$category['image'])
                ->assertSee(route('categories.index'));
        }
    }

    public function test_category_cards_link_to_the_shared_category_detail_route(): void
    {
        $categories = config('gunmatch_categories');
        $home = $this->get(route('home'))->assertOk();
        $index = $this->get(route('categories.index'))->assertOk();

        foreach ($categories as $category) {
            $url = route('categories.show', ['slug' => $category['slug']]);
            $home->assertSee($url);
            $index->assertSee($url);
        }
    }

    public function test_unknown_category_slug_returns_not_found(): void
    {
        $this->get('/categories/not-a-real-category')
            ->assertNotFound()
            ->assertSee('ページが見つかりません');
    }

    public function test_category_detail_route_does_not_change_existing_page_urls(): void
    {
        $this->assertSame('/categories', parse_url(route('categories.index'), PHP_URL_PATH));
        $this->assertSame('/categories/shotgun', parse_url(route('categories.show', 'shotgun'), PHP_URL_PATH));
        $this->get(route('categories.index'))->assertOk();
    }

    public function test_static_pages_link_only_to_registered_local_routes(): void
    {
        $pageRoutes = [
            'categories.index',
            'outdoor-samples.index',
            'about', 'guide', 'safety', 'prohibited-items', 'faq', 'terms', 'privacy', 'contact',
            'login', 'register', 'password.request', 'mypage', 'mypage.profile',
            'mypage.profile.public', 'mypage.profile.preview', 'mypage.settings',
            'mypage.security', 'mypage.account', 'mypage.notifications', 'mypage.announcements',
        ];
        $knownPaths = array_fill_keys(
            [
                ...array_map(fn (string $name): string => parse_url(route($name), PHP_URL_PATH) ?: '/', ['home', ...$pageRoutes]),
                ...array_map(fn (array $category): string => '/categories/'.$category['slug'], config('gunmatch_categories')),
                ...array_map(fn (array $item): string => '/outdoor-samples/'.$item['slug'], config('gunmatch_samples.outdoorItems')),
            ],
            true,
        );

        $memberRoutes = array_values(array_filter(
            $pageRoutes,
            fn (string $name): bool => str_starts_with($name, 'mypage'),
        ));

        foreach (['home', ...array_diff($pageRoutes, $memberRoutes), ...$memberRoutes] as $name) {
            $request = in_array($name, $memberRoutes, true)
                ? $this->actingAs(User::factory()->create())->get(route($name))
                : $this->get(route($name));
            $html = $request->assertOk()->getContent();
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

    public function test_outdoor_sample_list_and_details_share_their_templates(): void
    {
        $items = config('gunmatch_samples.outdoorItems');
        $this->assertCount(6, $items);

        $list = $this->get(route('outdoor-samples.index'))
            ->assertOk()
            ->assertViewIs('pages.outdoor-samples')
            ->assertSee('架空サンプル')
            ->assertSee('購入、譲渡、価格提示、問い合わせ、取引連絡、マッチング機能は提供していません。');

        foreach ($items as $item) {
            $detailUrl = route('outdoor-samples.show', ['slug' => $item['slug']]);
            $list->assertSee($item['title'])->assertSee($detailUrl);

            $this->get($detailUrl)
                ->assertOk()
                ->assertViewIs('pages.outdoor-sample')
                ->assertViewHas('item', $item)
                ->assertSee($item['title'])
                ->assertSee($item['description'])
                ->assertSee('assets/img/dummy/outdoor/'.$item['image'])
                ->assertSee('サンプル一覧へ戻る')
                ->assertDontSee('価格：');
        }
    }

    public function test_unknown_outdoor_sample_slug_returns_not_found(): void
    {
        $this->get('/outdoor-samples/unknown-sample')
            ->assertNotFound()
            ->assertSee('ページが見つかりません');
    }

    public function test_member_pages_use_real_account_data_and_authentication(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('登録済みのメールアドレスとパスワードでログインしてください。')
            ->assertSee('method="POST"', false)
            ->assertSee('name="_token"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('メールアドレスの確認が必要です')
            ->assertSee('個人会員')
            ->assertSee('銃砲店会員')
            ->assertSee('name="member_type"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('name="password_confirmation"', false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('パスワード再設定の案内を送信します。')
            ->assertSee('method="POST"', false);

        $this->get(route('mypage'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create(['name' => 'ログイン中の会員', 'member_type' => 'business']);

        $this->actingAs($user)->get(route('mypage'))
            ->assertOk()
            ->assertSee('ログイン中の会員')
            ->assertSee('銃砲店会員')
            ->assertSee($user->email);

        $this->get(route('mypage.profile.public'))
            ->assertOk()
            ->assertSee('ログイン中の会員')
            ->assertSee('第三者への公開機能はありません');

        $this->get(route('mypage.profile.preview'))
            ->assertOk()
            ->assertSee('ログイン中の会員');

        $this->get(route('mypage.security'))
            ->assertOk()
            ->assertSee('現在のパスワード')
            ->assertSee('新しいパスワード');

        $this->get(route('mypage.announcements'))
            ->assertOk()
            ->assertSee('実際のお知らせや配信履歴ではありません')
            ->assertSee('お知らせ機能は未実装です');

        $this->get(route('mypage.profile.preview'))
            ->assertOk()
            ->assertSee('公開プロフィール表示へ')
            ->assertDontSee('プレビュー表示へ');
    }

    public function test_mypage_navigation_marks_each_current_page_once(): void
    {
        $pages = [
            'mypage' => 'home',
            'mypage.profile' => 'profile',
            'mypage.profile.public' => 'profile-public',
            'mypage.profile.preview' => 'profile-preview',
            'mypage.settings' => 'settings',
            'mypage.security' => 'security',
            'mypage.account' => 'account',
            'mypage.notifications' => 'notifications',
            'mypage.announcements' => 'announcements',
        ];

        foreach ($pages as $routeName => $activeKey) {
            $html = $this->actingAs(User::factory()->create())->get(route($routeName))->assertOk()->getContent();
            preg_match('/<nav class="mypage-nav" aria-label="マイページメニュー">(.*?)<\/nav>/s', $html, $navigation);
            preg_match_all('/<a\b[^>]*>.*?<\/a>/s', $navigation[1] ?? '', $menuLinks);
            $activeLinks = array_values(array_filter(
                $menuLinks[0] ?? [],
                fn (string $link): bool => str_contains($link, 'class="is-active"'),
            ));
            preg_match('/href="([^"]+)"/', $activeLinks[0] ?? '', $activeLink);

            $this->assertSame(1, substr_count($navigation[1] ?? '', 'aria-current="page"'), "Expected one current-page marker on {$routeName}");
            $this->assertSame(1, substr_count($navigation[1] ?? '', 'class="is-active"'), "Expected one active navigation item on {$routeName}");
            $this->assertSame(
                parse_url(route($routeName), PHP_URL_PATH),
                isset($activeLink[1]) ? parse_url($activeLink[1], PHP_URL_PATH) : null,
                "Expected the {$activeKey} navigation item to be current",
            );
        }
    }

    public function test_category_overview_uses_existing_categories_without_transaction_links(): void
    {
        $response = $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('現在、掲載・検索・譲渡・取得を支援する機能は提供していません。')
            ->assertSee('散弾銃')
            ->assertSee('ライフル')
            ->assertSee('ハーフライフル')
            ->assertSee('エアライフル')
            ->assertSee('銃身')
            ->assertSee('アクセサリー')
            ->assertSee('無可動銃');

        $response->assertDontSee('商品を検索する')
            ->assertDontSee('取引相手に問い合わせる');
    }

    public function test_unknown_page_uses_custom_not_found_page(): void
    {
        $this->get('/not-a-real-gunmatch-page')
            ->assertNotFound()
            ->assertSee('ページが見つかりません')
            ->assertSee(route('home'));
    }
}
