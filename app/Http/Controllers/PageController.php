<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View|Response|RedirectResponse
    {
        if ($slug === 'new-member') {
            return redirect()->route('register', status: 301);
        }

        if ($slug === 'give') {
            return redirect()->route('give', status: 301);
        }

        $page = PageContext::resolve($slug);

        if ($page === null) {
            throw (new ModelNotFoundException)->setModel(Page::class);
        }

        $template = match ($page->template) {
            'home' => 'pages.home',
            'about' => 'pages.about',
            'contact' => 'pages.contact',
            'form' => 'pages.form',
            'full-width' => 'pages.full-width',
            default => 'pages.show',
        };

        return view($template, compact('page'));
    }
}
