<?php

namespace App\Http\Controllers;

use App\Services\PageContext;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $albums = \App\Models\GalleryAlbum::query()
            ->where('status', 'published')
            ->withCount('photos')
            ->orderBy('sort_order')
            ->paginate(12);

        return PageContext::view('gallery.index', 'gallery', compact('albums'));
    }

    public function show(string $slug): View
    {
        $album = \App\Models\GalleryAlbum::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['photos' => fn ($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        return view('gallery.show', compact('album'));
    }
}
