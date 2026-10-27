<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.categories', [
            'categories' => config('gunmatch_categories'),
        ]);
    }

    public function show(string $slug): View
    {
        $category = collect(config('gunmatch_categories'))->firstWhere('slug', $slug);
        abort_if($category === null, 404);

        return view('pages.category', [
            'category' => $category,
        ]);
    }
}
