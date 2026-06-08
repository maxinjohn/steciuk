<?php

namespace App\Http\Controllers;

use App\Services\PageContext;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $columns = ['id', 'title', 'slug', 'starts_at', 'ends_at', 'location', 'featured_image', 'category', 'status'];

        $upcoming = \App\Models\Event::query()
            ->select($columns)
            ->published()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->paginate(12);

        $past = \App\Models\Event::query()
            ->select($columns)
            ->published()
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at')
            ->limit(6)
            ->get();

        return PageContext::view('events.index', 'events', compact('upcoming', 'past'));
    }

    public function show(string $slug): View
    {
        $event = \App\Models\Event::query()->where('slug', $slug)->published()->firstOrFail();

        return view('events.show', compact('event'));
    }
}
