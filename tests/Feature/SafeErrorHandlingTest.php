<?php

namespace Tests\Feature;

use App\Services\HomePageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SafeErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_with_cached_home_data(): void
    {
        Cache::flush();
        HomePageData::forget();

        $first = $this->get(route('home'));
        $first->assertOk();

        $second = $this->get(route('home'));
        $second->assertOk();
        $second->assertDontSee('ErrorException', false);
    }

    public function test_error_responses_do_not_expose_environment_variables(): void
    {
        config(['security.expose_exception_details' => false]);

        $response = $this->get('/this-route-does-not-exist-'.uniqid());

        $response->assertNotFound();
        $body = $response->getContent();

        $this->assertStringNotContainsString('APP_KEY', $body);
        $this->assertStringNotContainsString('DB_PASSWORD', $body);
        $this->assertStringNotContainsString('$_ENV', $body);
        $this->assertStringNotContainsString('$_SERVER', $body);
        $this->assertStringNotContainsString('unserialize()', $body);
        $this->assertStringNotContainsString('vendor/laravel', $body);
    }

    public function test_server_errors_use_safe_page_without_stack_trace(): void
    {
        config(['security.expose_exception_details' => false]);

        \Illuminate\Support\Facades\Route::get('/__test/safe-error', function () {
            throw new \RuntimeException('Sensitive internal detail that must not leak');
        });

        $response = $this->get('/__test/safe-error');

        $response->assertStatus(500);
        $body = $response->getContent();

        $this->assertStringContainsString('Something went wrong', $body);
        $this->assertStringNotContainsString('Sensitive internal detail', $body);
        $this->assertStringNotContainsString('RuntimeException', $body);
    }
}
