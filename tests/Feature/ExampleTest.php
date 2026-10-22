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
            ->assertSee('GunMatchは、狩猟用銃器・銃身・関連用品を対象とするマッチングサービスとして構想されています。')
            ->assertSee('散弾銃')
            ->assertSee('ライフル')
            ->assertSee('ハーフライフル')
            ->assertSee('エアライフル')
            ->assertSee('銃身')
            ->assertSee('アクセサリー')
            ->assertSee('無可動銃')
            ->assertSee(asset('assets/img/categories/category_shotgun.png'))
            ->assertSee('GunMatchが選ばれる理由')
            ->assertSee('法令を尊重する')
            ->assertSee('情報を丁寧に伝える')
            ->assertSee('安全を大切にする')
            ->assertSee('共通の趣味でつながる')
            ->assertSee('利用の流れ')
            ->assertSee('構想を知る')
            ->assertSee('法令を確認する')
            ->assertSee('安全を大切にする')
            ->assertSee('狩猟という共通の趣味が、新しい出会いをつくる。')
            ->assertSee(asset('assets/img/branding/banner-hunting.webp'))
            ->assertSee('掲載・検索・マッチング・問い合わせ・取引の機能はありません。')
            ->assertSee('GunMatch｜狩猟用品のマッチングサービス')
            ->assertDontSee('本人確認済み')
            ->assertDontSee('許可証確認済み')
            ->assertDontSee('HUNTING & OUTDOOR GUIDE')
            ->assertDontSee('狩猟・アウトドアの情報ガイド')
            ->assertDontSee('検索する')
            ->assertDontSee('新規登録');
    }
}
