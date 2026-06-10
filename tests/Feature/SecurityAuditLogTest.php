<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SecurityAuditLog;
use App\Models\User;
use App\Services\MemberRegistrationService;
use App\Services\SecurityAuditLogService;
use App\Services\SecurityLogger;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityAuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_audit_log_stores_who_what_and_when(): void
    {
        $admin = User::factory()->create(['name' => 'Parish Admin', 'email' => 'admin@example.com']);
        $member = User::factory()->pending()->create(['name' => 'Pending Member', 'email' => 'member@example.com']);

        $this->actingAs($admin);

        app(MemberRegistrationService::class)->approve($member, $admin);

        $log = SecurityAuditLog::query()->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('member_account_approved', $log->action);
        $this->assertSame('Parish Admin', $log->actor_name);
        $this->assertSame('admin@example.com', $log->actor_email);
        $this->assertNotNull($log->summary);
        $this->assertStringContainsString('approved', strtolower($log->summary));
        $this->assertStringContainsString('Pending Member', $log->summary);
        $this->assertSame('User', $log->subject_type);
        $this->assertSame($member->id, $log->subject_id);
    }

    public function test_login_summary_includes_portal(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        SecurityLogger::audit('user_login', actor: $admin, context: [
            'portal' => SecurityLogger::adminPortalLabel(),
        ]);

        $log = SecurityAuditLog::query()->latest('id')->firstOrFail();

        $this->assertStringContainsString('parish admin panel', $log->summary);
        $this->assertSame('admin@example.com', $log->actor_email);
    }

    public function test_admin_login_is_written_immediately_without_terminate(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        SecurityLogger::audit('user_login', actor: $admin, context: [
            'portal' => SecurityLogger::adminPortalLabel(),
        ]);

        $this->assertSame(1, SecurityAuditLog::query()->count());
    }

    public function test_filament_record_created_is_audited_in_admin_panel(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $member = User::factory()->create(['role' => UserRole::Member]);

        $this->actingAs($admin);

        SecurityLogger::audit('admin_record_created', subject: $member, context: [
            'resource' => 'User',
            'resource_id' => $member->id,
            'portal' => SecurityLogger::adminPortalLabel(),
        ]);

        $log = SecurityAuditLog::query()->latest('id')->firstOrFail();

        $this->assertSame('admin_record_created', $log->action);
        $this->assertStringContainsString('created', strtolower($log->summary));
    }

    public function test_super_admin_can_purge_activity_log_entries_on_or_before_date(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        SecurityAuditLog::query()->create([
            'action' => 'user_login',
            'summary' => 'Old entry',
            'severity' => 'info',
            'created_at' => now()->subDays(30),
        ]);

        SecurityAuditLog::query()->create([
            'action' => 'user_login',
            'summary' => 'Recent entry',
            'severity' => 'info',
            'created_at' => now()->subDay(),
        ]);

        $purged = app(SecurityAuditLogService::class)->purgeOnOrBefore(
            $admin,
            now()->subDays(30),
        );

        $this->assertSame(1, $purged);
        $this->assertSame(2, SecurityAuditLog::query()->count());
        $this->assertDatabaseHas('security_audit_logs', ['summary' => 'Recent entry']);

        $purgeLog = SecurityAuditLog::query()->where('action', 'security_audit_log_purged')->first();
        $this->assertNotNull($purgeLog);
        $this->assertStringContainsString('1', $purgeLog->summary);
    }

    public function test_activity_log_purge_rejects_dates_within_retention_window(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        SecurityAuditLog::query()->create([
            'action' => 'user_login',
            'summary' => 'Old entry',
            'severity' => 'info',
            'created_at' => now()->subDays(40),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('only entries older than 30 days');

        app(SecurityAuditLogService::class)->purgeOnOrBefore(
            $admin,
            now()->subDays(7),
        );
    }

    public function test_activity_log_purge_keeps_entries_from_last_thirty_days(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        SecurityAuditLog::query()->create([
            'action' => 'user_login',
            'summary' => 'Protected entry',
            'severity' => 'info',
            'created_at' => now()->subDays(10),
        ]);

        $purged = app(SecurityAuditLogService::class)->purgeOnOrBefore(
            $admin,
            now()->subDays(30),
        );

        $this->assertSame(0, $purged);
        $this->assertDatabaseHas('security_audit_logs', ['summary' => 'Protected entry']);
    }

    public function test_non_super_admin_cannot_purge_activity_log(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);

        SecurityAuditLog::query()->create([
            'action' => 'user_login',
            'summary' => 'Old entry',
            'severity' => 'info',
            'created_at' => now()->subDays(30),
        ]);

        $this->expectException(\InvalidArgumentException::class);

        app(SecurityAuditLogService::class)->purgeOnOrBefore(
            $editor,
            now()->subDays(31),
        );
    }

    public function test_super_admin_can_view_audit_log_with_array_metadata(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $log = SecurityAuditLog::query()->create([
            'action' => 'user_login',
            'summary' => 'Signed in to parish admin panel',
            'severity' => 'info',
            'metadata' => [
                'portal' => SecurityLogger::adminPortalLabel(),
                'actor_email' => 'admin@example.com',
            ],
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url("security-audit-logs/{$log->id}"))
            ->assertOk()
            ->assertSee('parish admin panel', false);
    }

    public function test_super_admin_can_view_audit_log_with_legacy_string_metadata(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $log = SecurityAuditLog::query()->create([
            'action' => 'user_login',
            'summary' => 'Legacy metadata entry',
            'severity' => 'info',
            'created_at' => now(),
        ]);

        DB::table('security_audit_logs')
            ->where('id', $log->id)
            ->update([
                'metadata' => json_encode([
                    'portal' => SecurityLogger::adminPortalLabel(),
                ]),
            ]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url("security-audit-logs/{$log->id}"))
            ->assertOk()
            ->assertSee('Legacy metadata entry', false);
    }
}
