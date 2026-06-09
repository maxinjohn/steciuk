<?php

namespace App\Http\Controllers;

use App\Models\LeadershipMember;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View|Response
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->published()
            ->with(['contentBlocks' => fn ($q) => $q->where('is_visible', true)])
            ->firstOrFail();

        $template = match ($page->template) {
            'home' => 'pages.home',
            'about' => 'pages.about',
            'contact' => 'pages.contact',
            'form' => 'pages.form',
            'full-width' => 'pages.full-width',
            default => 'pages.show',
        };

        $leadershipMembers = $page->slug === 'leadership'
            ? LeadershipMember::query()->where('is_visible', true)->orderBy('sort_order')->get()
            : collect();

        return view($template, compact('page', 'leadershipMembers'));
    }
}
