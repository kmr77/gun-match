<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class OutdoorSampleController extends Controller
{
    public function index(): View
    {
        return view('pages.outdoor-samples', [
            'items' => config('gunmatch_samples.outdoorItems'),
        ]);
    }

    public function show(string $slug): View
    {
        $item = collect(config('gunmatch_samples.outdoorItems'))->firstWhere('slug', $slug);
        abort_if($item === null, 404);

        return view('pages.outdoor-sample', [
            'item' => $item,
        ]);
    }
}
