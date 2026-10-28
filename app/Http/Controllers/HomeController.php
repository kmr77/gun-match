<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = config('gunmatch_categories');
        $samples = config('gunmatch_samples.home');
        $offerItems = $samples['offerItems'];
        $wantedItems = $samples['wantedItems'];

        return view('home', compact('categories', 'offerItems', 'wantedItems'));
    }
}
