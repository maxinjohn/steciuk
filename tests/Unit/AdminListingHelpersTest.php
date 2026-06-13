<?php

namespace Tests\Unit;

use App\Enums\DonationStatus;
use App\Enums\FormType;
use App\Filament\Support\AdminTableSearch;
use App\Models\Donation;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListingHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_submission_exposes_submitter_details(): void
    {
        $submission = FormSubmission::query()->create([
            'form_type' => FormType::Contact,
            'data' => [
                'name' => 'Jane Parish',
                'email' => 'jane@example.com',
                'message' => 'Please call me about Sunday school.',
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);

        $this->assertSame('Jane Parish', $submission->submitterName());
        $this->assertSame('jane@example.com', $submission->submitterEmail());
        $this->assertStringContainsString('Sunday school', (string) $submission->previewText());
    }

    public function test_donation_search_matches_reference_and_donor_email(): void
    {
        $donor = User::factory()->create([
            'email' => 'donor@example.com',
            'first_name' => 'Gift',
            'last_name' => 'Giver',
        ]);

        $match = Donation::query()->create([
            'user_id' => $donor->id,
            'amount' => 25,
            'currency' => 'GBP',
            'donated_on' => now()->toDateString(),
            'status' => DonationStatus::Approved->value,
            'method' => 'bank_transfer',
            'reference' => 'STE-2026-001',
        ]);

        Donation::query()->create([
            'user_id' => User::factory()->create()->id,
            'amount' => 10,
            'currency' => 'GBP',
            'donated_on' => now()->toDateString(),
            'status' => DonationStatus::Approved->value,
            'method' => 'bank_transfer',
            'reference' => 'OTHER-REF',
        ]);

        $byReference = Donation::query();
        AdminTableSearch::applyDonations($byReference, 'STE-2026-001');
        $this->assertTrue($byReference->whereKey($match->id)->exists());

        $byEmail = Donation::query();
        AdminTableSearch::applyDonations($byEmail, 'donor@example.com');
        $this->assertTrue($byEmail->whereKey($match->id)->exists());
    }
}
