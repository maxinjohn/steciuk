<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicArtRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_topic_art_route_returns_cacheable_svg(): void
    {
        $response = $this->get(route('topic-art.show', [
            'topic' => 'event',
            'seed' => 'new-parish-gathering',
            't' => 'New Parish Gathering',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml; charset=utf-8');
        $this->assertStringContainsString('image/svg+xml', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));
        $this->assertStringStartsWith('<svg', $response->getContent());
    }
}
