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
}
