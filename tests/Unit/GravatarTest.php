<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Gravatar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GravatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_gravatar_url_uses_normalized_email_hash(): void
    {
        $url = Gravatar::url('Member@Example.com');

        $this->assertSame(
            'https://www.gravatar.com/avatar/'.md5('member@example.com').'?s=256&d=404',
            $url,
        );
    }

    public function test_gravatar_exists_returns_false_when_head_request_is_not_successful(): void
    {
        Http::fake([
            'gravatar.com/*' => Http::response('', 404),
        ]);

        $this->assertFalse(Gravatar::exists('missing@example.com'));
    }

    public function test_gravatar_exists_returns_true_when_head_request_succeeds(): void
    {
        Http::fake([
            'gravatar.com/*' => Http::response('', 200),
        ]);

        $this->assertTrue(Gravatar::exists('found@example.com'));
    }

    public function test_avatar_url_falls_back_to_initials_when_gravatar_is_missing(): void
    {
        Http::fake([
            'gravatar.com/*' => Http::response('', 404),
        ]);

        $user = User::factory()->create([
            'email' => 'no-gravatar@example.com',
        ]);

        $this->assertNull($user->avatarUrl());
        $this->assertFalse($user->usesGravatar());
    }

    public function test_avatar_url_uses_gravatar_when_present(): void
    {
        Http::fake([
            'gravatar.com/*' => Http::response('', 200),
        ]);

        $user = User::factory()->create([
            'email' => 'found@example.com',
        ]);

        $this->assertSame($user->gravatarUrl(), $user->avatarUrl());
        $this->assertTrue($user->usesGravatar());
    }
}
