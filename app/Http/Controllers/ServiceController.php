<?php

namespace App\Http\Controllers;

use App\Services\PageContext;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = \App\Models\Service::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return PageContext::view('services.index', 'service-times', compact('services'));
    }
}
