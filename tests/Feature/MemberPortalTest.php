<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\FormType;
use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\Setting;
use App\Models\User;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_family_tab_without_household(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin, 'family_id' => null]);

        $this->actingAs($admin)
            ->get('/account?tab=family')
            ->assertOk()
            ->assertSee('Create family', false);
    }

    public function test_member_can_start_parish_conversation_from_portal(): void
    {
        Mail::fake();
        Setting::set('contact_email', 'office@steciuk.org', 'contact');

        $member = User::factory()->create([
            'role' => UserRole::Member,
            'account_status' => AccountStatus::Approved,
        ]);

        Livewire::actingAs($member)
            ->test(\App\Livewire\Account\ParishMessagesManager::class)
            ->set('newSubject', 'Baptism question')
            ->set('newMessage', 'Could we discuss baptism dates?')
            ->call('startConversation')
            ->assertSet('sent', true);

        $this->assertDatabaseHas('conversations', [
            'user_id' => $member->id,
            'subject' => 'Baptism question',
            'source' => 'member_portal',
        ]);

        $this->assertDatabaseHas('messages', [
            'body' => 'Could we discuss baptism dates?',
        ]);
    }

    public function test_account_page_renders_messages_and_family_tabs(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin, 'family_id' => null]);

        $this->actingAs($admin)
            ->get('/account')
            ->assertOk()
            ->assertSee('member-portal-tab', false)
            ->assertSee('>Messages<', false)
            ->assertSee('>Family<', false)
            ->assertSee('member-portal-shell', false);

        $this->actingAs($admin)
            ->get('/account?tab=messages')
            ->assertOk()
            ->assertSee('Messages to parish', false);

        $this->actingAs($admin)
            ->get('/account?tab=family')
            ->assertOk()
            ->assertSee('Create family', false);
    }

    public function test_admin_inbox_lists_conversations(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->get(\App\Support\AdminPanelConfig::url('conversations'))
            ->assertOk();
    }

    public function test_contact_form_creates_unified_conversation(): void
    {
        Mail::fake();
        Setting::set('contact_email', 'office@steciuk.org', 'contact');

        Livewire::test(\App\Livewire\Forms\ContactForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('message', 'Hello parish office')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('form_submissions', [
            'form_type' => FormType::Contact->value,
        ]);

        $conversation = Conversation::query()->first();
        $this->assertNotNull($conversation);
        $this->assertSame('jane@example.com', $conversation->guest_email);
        $this->assertTrue($conversation->unread_by_admin);
    }
}
