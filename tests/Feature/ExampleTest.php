<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_uses_gunmatch_branding_without_marketplace_actions(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('狩猟の道具と人をつなぐ')
            ->assertSee('HUNTING & SHOOTING MARKETPLACE')
            ->assertSee('狩猟の道具と人をつなぐ、GunMatch。')
            ->assertSee('散弾銃')
            ->assertSee('ライフル')
            ->assertSee('ハーフライフル')
            ->assertSee('エアライフル')
            ->assertSee('銃身')
            ->assertSee('アクセサリー')
            ->assertSee('無可動銃')
            ->assertSee(asset('assets/img/categories/category_shotgun.png'))
            ->assertSee('新着の「譲る」（売りたい）')
            ->assertSee('新着の「探す」（買いたい）')
            ->assertSee('撥水シェルジャケット')
            ->assertSee('保温フリースジャケット')
            ->assertSee(asset('assets/img/dummy/outdoor/jacket.svg'))
            ->assertSee(asset('assets/img/dummy/outdoor/gloves.svg'))
            ->assertSee('一般的なアウトドア用品の仮データです。実在の商品・募集ではありません。')
            ->assertDontSee('listing-card__link')
            ->assertDontSee('弾薬');

        $response->assertSeeInOrder([
            'id="categories"',
            'id="offer-items"',
            'id="wanted-items"',
            'id="reasons"',
            'id="howto"',
            'id="bottom-banner-title"',
        ]);

        $html = $response->getContent();
        $this->assertSame(12, substr_count($html, 'class="listing-card"'));
        $this->assertSame(6, substr_count($html, 'listing-card__kind--offer'));
        $this->assertSame(6, substr_count($html, 'listing-card__kind--wanted'));

        $response
            ->assertSee('GunMatchが選ばれる理由')
            ->assertSee('法令を大切に')
            ->assertSee('情報をわかりやすく')
            ->assertSee('安全を第一に')
            ->assertSee('趣味を通じたつながり')
            ->assertSee('利用の流れ')
            ->assertSee('GunMatchを知る')
            ->assertSee('法令を確認する')
            ->assertSee('安全に楽しむ')
            ->assertSee('狩猟という共通の趣味が、新しい出会いをつくる。')
            ->assertDontSee(asset('assets/img/branding/banner-hunting.webp'))
            ->assertSee('GunMatch｜狩猟の道具と人をつなぐ')
            ->assertDontSee('構想されたプロジェクトです')
            ->assertDontSee('このページはデザイン確認用')
            ->assertDontSee('掲載・検索・マッチング・問い合わせ・取引の機能はありません')
            ->assertDontSee('本人確認済み')
            ->assertDontSee('許可証確認済み')
            ->assertDontSee('HUNTING & OUTDOOR GUIDE')
            ->assertDontSee('狩猟・アウトドアの情報ガイド')
            ->assertDontSee('検索する')
            ->assertDontSee('新規登録');
    }

    public function test_unregistered_page_returns_not_found(): void
    {
        $this->get('/gm006-route-check-not-found')
            ->assertNotFound()
            ->assertSee('ページが見つかりません');
    }
}
