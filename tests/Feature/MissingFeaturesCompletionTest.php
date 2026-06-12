<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\News;
use App\Models\User;
use App\Services\ContentApprovalService;
use App\Services\ParishEmailService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissingFeaturesCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_editor_submitted_content_appears_in_approval_queue(): void
    {
        News::factory()->create(['status' => PublishStatus::PendingReview->value]);

        $this->assertSame(1, app(ContentApprovalService::class)->pendingCount());
    }

    public function test_admin_can_approve_pending_content(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $news = News::factory()->create(['status' => PublishStatus::PendingReview->value]);

        app(ContentApprovalService::class)->approve(News::class, $news->id, $admin);

        $this->assertSame(PublishStatus::Published, $news->fresh()->status);
        $this->assertSame(0, app(ContentApprovalService::class)->pendingCount());
    }

    public function test_content_approvals_page_is_available_to_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        News::factory()->create(['status' => PublishStatus::PendingReview->value]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('content-approvals'))
            ->assertOk()
            ->assertSee('Content awaiting review', false);
    }

    public function test_registration_welcome_email_template_is_available(): void
    {
        $template = app(ParishEmailService::class)->resolve(ParishEmailService::PARISH_WELCOME);

        $this->assertStringContainsString('{site_name}', $template['subject']);
        $this->assertStringContainsString('review your details', strtolower($template['body']));
    }

    public function test_admin_created_account_email_template_is_available(): void
    {
        $template = app(ParishEmailService::class)->resolve(ParishEmailService::ACCOUNT_ADDED_BY_ADMIN);

        $this->assertStringContainsString('{site_name}', $template['subject']);
        $this->assertStringContainsString('{login_url}', $template['body']);
    }
}
