<?php

namespace Tests\Unit;

use App\Support\FaithComfortVerseBuckets;
use Tests\TestCase;

class FaithComfortVerseBucketsTest extends TestCase
{
    public function test_split_and_merge_round_trip_preserves_scopes(): void
    {
        $original = [
            ['ref' => 'Psalm 23:1', 'text' => 'Global verse', 'only_on' => ''],
            ['ref' => 'John 3:16', 'text' => 'Error verse', 'only_on' => 'error'],
            ['ref' => 'Isaiah 40:31', 'text' => 'Maintenance verse', 'only_on' => 'maintenance'],
            ['ref' => 'Jeremiah 29:11', 'text' => 'Launch verse', 'only_on' => 'launch'],
            ['ref' => 'Philippians 4:7', 'text' => 'Path verse', 'only_on' => '/contact'],
        ];

        $formData = FaithComfortVerseBuckets::split($original);
        $merged = FaithComfortVerseBuckets::merge($formData);

        $this->assertSame('Global verse', $merged[0]['text']);
        $this->assertSame('', $merged[0]['only_on']);
        $this->assertSame('error', collect($merged)->firstWhere('text', 'Error verse')['only_on']);
        $this->assertSame('maintenance', collect($merged)->firstWhere('text', 'Maintenance verse')['only_on']);
        $this->assertSame('launch', collect($merged)->firstWhere('text', 'Launch verse')['only_on']);
        $this->assertSame('/contact', collect($merged)->firstWhere('text', 'Path verse')['only_on']);
    }
}
