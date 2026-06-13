<?php

namespace App\Http\Controllers;

use App\Support\TopicArtGenerator;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TopicArtController extends Controller
{
    public function __invoke(string $topic, string $seed): Response
    {
        $topic = TopicArtGenerator::normalizeTopic($topic);
        $seed = Str::slug($seed) ?: 'default';
        $title = request()->string('t')->toString();
        $contentHint = request()->string('c')->toString();

        $svg = TopicArtGenerator::render(
            $topic,
            $seed,
            $title !== '' ? $title : null,
            $contentHint !== '' ? $contentHint : null,
        );

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
