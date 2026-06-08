<?php

namespace App\Http\Controllers;

use App\Services\PageContext;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(): View
    {
        $resources = \App\Models\Resource::query()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return PageContext::view('resources.index', 'resources', compact('resources'));
    }
}
