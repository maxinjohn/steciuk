<?php

namespace Tests\Unit;

use App\Support\ContextScripture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContextScriptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_event_scripture_for_events_routes(): void
    {
        $this->get(route('events.index'));

        $scripture = ContextScripture::forRequest();

        $this->assertSame('Fellowship', $scripture['kicker']);
        $this->assertStringContainsString('meeting together', $scripture['text']);
        $this->assertSame('Hebrews 10:25', $scripture['ref']);
    }

    public function test_empty_state_for_sermons(): void
    {
        $comfort = ContextScripture::emptyStateFor('sermons');

        $this->assertStringContainsString('word', strtolower($comfort['text']));
        $this->assertSame('Matthew 4:4', $comfort['ref']);
    }

    public function test_empty_state_for_services_and_resources(): void
    {
        $services = ContextScripture::emptyStateFor('services');
        $resources = ContextScripture::emptyStateFor('resources');

        $this->assertSame('Psalm 122:1', $services['ref']);
        $this->assertSame('2 Timothy 3:16', $resources['ref']);
        $this->assertSame('2 Corinthians 9:7', ContextScripture::emptyStateFor('give')['ref']);
    }

    public function test_divine_whispers_pool_is_populated(): void
    {
        $this->assertNotEmpty(ContextScripture::divineWhispers());
    }
}
