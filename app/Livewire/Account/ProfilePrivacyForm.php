<?php

namespace App\Livewire\Account;

use App\Models\User;
use App\Services\DataProtectionService;
use App\Services\SecurityLogger;
use App\Support\GdprConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfilePrivacyForm extends Component
{
    public bool $marketing_consent = false;

    public bool $erasureRequested = false;

    public bool $savedMarketing = false;

    public bool $erasureSubmitted = false;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $this->marketing_consent = (bool) $user->marketing_consent;
        $this->erasureRequested = $user->hasErasureRequest();
    }

    public function exportData(DataProtectionService $dataProtectionService): StreamedResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        abort_if($user->isAnonymized(), 403);

        $payload = $dataProtectionService->exportPersonalData($user);

        SecurityLogger::audit('gdpr_data_exported', actor: $user, subject: $user, context: [
            'portal' => SecurityLogger::detectPortal(),
        ]);

        $filename = 'steciuk-parish-data-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(
            static function () use ($payload): void {
                echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            },
            $filename,
            ['Content-Type' => 'application/json'],
        );
    }

    public function requestErasure(DataProtectionService $dataProtectionService): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        abort_if($user->isAnonymized(), 422);

        $dataProtectionService->requestErasure($user);

        $this->erasureRequested = true;
        $this->erasureSubmitted = true;
    }

    public function saveMarketingConsent(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        abort_if($user->isAnonymized(), 403);

        $user->update([
            'marketing_consent' => $this->marketing_consent,
            'marketing_consent_at' => $this->marketing_consent ? now() : null,
        ]);

        SecurityLogger::audit('gdpr_marketing_consent_updated', actor: $user, subject: $user, context: [
            'marketing_consent' => $this->marketing_consent,
            'portal' => SecurityLogger::detectPortal(),
        ]);

        $this->savedMarketing = true;
    }

    public function render()
    {
        return view('livewire.account.profile-privacy-form', [
            'privacyPolicyUrl' => GdprConfig::privacyPolicyUrl(),
            'dataProtectionEmail' => GdprConfig::dataProtectionContactEmail(),
            'icoComplaintUrl' => GdprConfig::icoComplaintUrl(),
        ]);
    }
}
