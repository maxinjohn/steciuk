<?php

namespace App\Http\Controllers;

use App\Services\PageContext;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $articles = \App\Models\News::query()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return PageContext::view('news.index', 'news', compact('articles'));
    }

    public function show(string $slug): View
    {
        $article = \App\Models\News::query()->where('slug', $slug)->published()->firstOrFail();

        return view('news.show', compact('article'));
    }
}
