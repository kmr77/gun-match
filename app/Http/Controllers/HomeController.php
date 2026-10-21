<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = [
            [
                'name' => '散弾銃',
                'image' => 'category_shotgun.png',
            ],
            [
                'name' => 'ライフル',
                'image' => 'category_rifle.png',
            ],
            [
                'name' => 'ハーフライフル',
                'image' => 'category_half_rifle.png',
            ],
            [
                'name' => 'エアライフル',
                'image' => 'category_air_rifle.png',
            ],
            [
                'name' => '銃身',
                'image' => 'category_barrel.png',
            ],
            [
                'name' => 'アクセサリー',
                'image' => 'category_accessories.png',
            ],
            [
                'name' => '無可動銃',
                'image' => 'category_deactivated_gun.png',
            ],
        ];

        return view('home', compact('categories'));
    }
}
