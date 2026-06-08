<?php

namespace App\Http\Controllers;

use App\Enums\PublishStatus;
use App\Services\PageContext;
use Illuminate\View\View;

class SermonController extends Controller
{
    public function index(): View
    {
        $sermons = \App\Models\Sermon::query()
            ->select(['id', 'title', 'speaker', 'preached_at', 'bible_passage', 'description', 'youtube_url', 'status'])
            ->where('status', PublishStatus::Published)
            ->with('media')
            ->orderByDesc('preached_at')
            ->paginate(12);

        return PageContext::view('sermons.index', 'sermons', compact('sermons'));
    }
}
