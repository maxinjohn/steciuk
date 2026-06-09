<?php

namespace App\Http\Controllers;

use App\Services\PageContext;
use Illuminate\View\View;

class MinistryController extends Controller
{
    public function index(): View
    {
        $ministries = \App\Models\Ministry::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        return PageContext::view('ministries.index', 'ministries', compact('ministries'));
    }

    public function show(string $slug): View
    {
        $ministry = \App\Models\Ministry::query()
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        return view('ministries.show', compact('ministry'));
    }
}
