<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_displays_static_information_categories_without_marketplace_actions(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('自然を知る。')
            ->assertSee('狩猟・アウトドアのテーマ')
            ->assertSee('散弾銃')
            ->assertSee('ライフル')
            ->assertSee('ハーフライフル')
            ->assertSee('エアライフル')
            ->assertSee('銃身')
            ->assertSee('アクセサリー')
            ->assertSee('無可動銃')
            ->assertSee(asset('assets/img/categories/category_shotgun.png'))
            ->assertSee('出品・譲渡・取得・検索・問い合わせには対応していません。')
            ->assertDontSee('検索する')
            ->assertDontSee('譲る（')
            ->assertDontSee('新規登録');
    }
}
