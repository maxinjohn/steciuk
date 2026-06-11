<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\FormSubmission;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\SitePaths;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_site_doctor_passes_on_fresh_install(): void
    {
        $this->artisan('site:doctor')
            ->assertSuccessful();
    }

    public function test_site_ensure_paths_creates_external_upload_tree(): void
    {
        $absolute = storage_path('framework/testing/prod-ready-'.bin2hex(random_bytes(4)));

        config([
            'site.paths.public_uploads' => $absolute,
            'filesystems.disks.public.root' => $absolute,
        ]);

        $this->artisan('site:ensure-paths')
            ->assertSuccessful();

        $this->assertDirectoryExists($absolute.'/settings/branding');

        File::deleteDirectory($absolute);
    }

    public function test_church_settings_loads_with_external_upload_path_from_config(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $absolute = storage_path('framework/testing/church-settings-'.bin2hex(random_bytes(4)));

        config([
            'site.paths.public_uploads' => $absolute,
            'filesystems.disks.public.root' => $absolute,
            'filesystems.links' => [
                public_path('storage') => $absolute,
            ],
        ]);

        $this->artisan('site:ensure-paths')->assertSuccessful();

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('church-settings'))
            ->assertOk();

        File::deleteDirectory($absolute);
    }

    public function test_form_submission_view_handles_legacy_string_data(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $submission = FormSubmission::query()->create([
            'form_type' => 'contact',
            'data' => [],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);

        DB::table('form_submissions')
            ->where('id', $submission->id)
            ->update(['data' => json_encode(['name' => 'Grace', 'message' => 'Peace'])]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url("form-submissions/{$submission->id}"))
            ->assertOk()
            ->assertSee('Grace', false);
    }

    public function test_configured_paths_use_site_config_not_runtime_env(): void
    {
        config(['site.paths.public_uploads' => '../site_data/example-uploads']);

        putenv('PUBLIC_STORAGE_PATH');
        unset($_ENV['PUBLIC_STORAGE_PATH'], $_SERVER['PUBLIC_STORAGE_PATH']);

        $this->assertSame(
            base_path('../site_data/example-uploads'),
            SitePaths::configuredPath('public_uploads'),
        );
    }
}
