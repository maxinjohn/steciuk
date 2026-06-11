<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Livewire\Auth\ForgotPasswordForm;
use App\Livewire\Auth\ResetPasswordForm;
use App\Models\Family;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class UserPasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_user_password_from_edit_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'role' => UserRole::Member,
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $member->getRouteKey()])
            ->callAction('setPassword', data: [
                'password' => 'NewSecurePass1!',
                'password_confirmation' => 'NewSecurePass1!',
            ])
            ->assertNotified();

        $this->assertTrue(Hash::check('NewSecurePass1!', $member->fresh()->password));
    }

    public function test_admin_can_send_password_reset_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'role' => UserRole::Member,
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $member->getRouteKey()])
            ->callAction('sendPasswordResetLink')
            ->assertNotified();

        Notification::assertSentTo($member, ResetPassword::class);
    }

    public function test_public_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $member = User::factory()->create([
            'email' => 'member@example.com',
            'role' => UserRole::Member,
        ]);

        Livewire::test(ForgotPasswordForm::class)
            ->set('email', 'member@example.com')
            ->call('sendResetLink')
            ->assertSet('sent', true);

        Notification::assertSentTo($member, ResetPassword::class);
    }

    public function test_public_forgot_password_sends_for_linked_household_member(): void
    {
        Notification::fake();

        $family = Family::query()->create(['name' => 'Test-Family']);

        $member = User::factory()->create([
            'email' => 'member@example.com',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'is_family_admin' => false,
        ]);

        Livewire::test(ForgotPasswordForm::class)
            ->set('email', 'member@example.com')
            ->call('sendResetLink')
            ->assertSet('sent', true);

        Notification::assertSentTo($member, ResetPassword::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'OldSecurePass1!',
            'role' => UserRole::Member,
        ]);

        $token = Password::createToken($member);

        Livewire::test(ResetPasswordForm::class, ['token' => $token])
            ->set('email', 'member@example.com')
            ->set('password', 'BrandNewPass1!')
            ->set('password_confirmation', 'BrandNewPass1!')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('BrandNewPass1!', $member->fresh()->password));
    }

    public function test_password_reset_link_uses_member_portal_route(): void
    {
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'role' => UserRole::Member,
        ]);

        $token = Password::createToken($member);
        $notification = new ResetPassword($token);
        $mail = $notification->toMail($member);

        $this->assertStringContainsString(route('password.reset', [
            'token' => $token,
            'email' => 'member@example.com',
        ], false), $mail->actionUrl);
    }
}
