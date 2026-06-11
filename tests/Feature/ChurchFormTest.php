<?php

namespace Tests\Feature;

use App\Enums\FormType;
use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ChurchFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::query()->firstOrCreate(
            ['email' => 'admin@steciuk.org'],
            User::factory()->make([
                'email' => 'admin@steciuk.org',
                'role' => UserRole::SuperAdmin,
            ])->getAttributes(),
        );

        Setting::set('contact_email', 'admin@steciuk.org', 'contact');
    }

    public function test_contact_form_submits_with_sync_queue(): void
    {
        Mail::fake();

        Livewire::test(\App\Livewire\Forms\ContactForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('message', 'Hello from the parish website.')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('form_submissions', [
            'form_type' => FormType::Contact->value,
        ]);

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_honeypot_blocks_bot_submissions(): void
    {
        Mail::fake();

        Livewire::test(\App\Livewire\Forms\ContactForm::class)
            ->set('website', 'http://spam.test')
            ->set('name', 'Bot')
            ->set('email', 'bot@example.com')
            ->set('message', 'Spam')
            ->call('submit')
            ->assertSet('submitted', false);

        $this->assertDatabaseCount('form_submissions', 0);
        Mail::assertNothingSent();
    }

    public function test_footer_receives_dynamic_service_locations_after_seed(): void
    {
        config(['site.seed.mode' => \App\Support\SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Manchester');
        $response->assertSee('Bristol');
    }
}
