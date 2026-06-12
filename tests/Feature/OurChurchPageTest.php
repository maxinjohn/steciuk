<?php

namespace Tests\Feature;

use App\Support\ReferenceSiteContent;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OurChurchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_our_church_page_shows_membership_panel_and_beliefs(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get('/our-church');

        $response->assertOk()
            ->assertSee('eauk-member-panel', false)
            ->assertSee('Evangelical Alliance member church', false)
            ->assertSee(ReferenceSiteContent::EAUK_MEMBERSHIP_NUMBER, false)
            ->assertSee('View EAUK church profile', false)
            ->assertSee('EAUK logo guidelines', false)
            ->assertSee('images/eauk/member-logo-medium.png', false)
            ->assertSee('Who We Are', false)
            ->assertSee('Evangelical Alliance Membership', false)
            ->assertSee('Holy Scripture', false)
            ->assertSee('our-church-nav', false)
            ->assertSee('faith-pillars', false)
            ->assertSee(ReferenceSiteContent::EAUK_CHURCH_URL, false);
    }
}
