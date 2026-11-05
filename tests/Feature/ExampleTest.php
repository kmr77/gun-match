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
            ->assertSee('GUNMATCH｜HUNTING &amp; SHOOTING', false)
            ->assertSee('狩猟の道具と人をつなぐ、GunMatch。')
            ->assertSee('散弾銃')
            ->assertSee('ライフル')
            ->assertSee('ハーフライフル')
            ->assertSee('エアライフル')
            ->assertSee('銃身')
            ->assertSee('アクセサリー')
            ->assertSee('無可動銃')
            ->assertSee(asset('assets/img/categories/category_shotgun.png'))
            ->assertSee('カードの表示サンプル（架空データ）')
            ->assertSee('撥水シェルジャケット')
            ->assertSee('保温フリースジャケット')
            ->assertSee(asset('assets/img/dummy/outdoor/jacket.svg'))
            ->assertSee(asset('assets/img/dummy/outdoor/gloves.svg'))
            ->assertSee('一般的なアウトドア用品を使った架空の表示例です。実在の商品・募集ではなく、譲渡・購入や問い合わせはできません。')
            ->assertDontSee('listing-card__link')
            ->assertDontSee('新着の「譲る」')
            ->assertDontSee('新着の「探す」')
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
        $this->assertSame(0, substr_count($html, 'listing-card__kind--offer'));
        $this->assertSame(0, substr_count($html, 'listing-card__kind--wanted'));

        $response
            ->assertSee('GunMatchが大切にすること')
            ->assertSee('法令を大切に')
            ->assertSee('情報をわかりやすく')
            ->assertSee('安全を第一に')
            ->assertSee('人と道具に向き合う')
            ->assertSee('GunMatchの基本姿勢')
            ->assertSee('ブランドの考え方')
            ->assertSee('法令を確認する')
            ->assertSee('安全と責任')
            ->assertSee('狩猟の道具と人に向き合うGunMatch。')
            ->assertDontSee(asset('assets/img/branding/banner-hunting.webp'))
            ->assertSee('GunMatch｜狩猟の道具と人をつなぐ')
            ->assertDontSee('構想されたプロジェクトです')
            ->assertDontSee('このページはデザイン確認用')
            ->assertDontSee('掲載・検索・マッチング・問い合わせ・取引の機能はありません')
            ->assertDontSee('本人確認済み')
            ->assertDontSee('許可証確認済み')
            ->assertDontSee('HUNTING & OUTDOOR GUIDE')
            ->assertDontSee('MARKETPLACE')
            ->assertDontSee('利用の流れ')
            ->assertDontSee('譲る</span>')
            ->assertDontSee('探す</span>')
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
