<div class="space-y-6">
    @if ($erasureSubmitted)
        <div class="member-alert member-alert--success" role="status">
            Your deletion request has been recorded. The parish office will review it and contact you if needed before your account is anonymised.
        </div>
    @endif

    @if ($savedMarketing)
        <div class="member-alert member-alert--success" role="status">
            Your communication preferences have been saved.
        </div>
    @endif

    <div>
        <h3 class="text-base font-semibold text-ink">Download your data</h3>
        <p class="mt-2 text-sm text-ink-muted">
            You can download a copy of the personal data we hold about you in your parish account, including profile details, consent records, and giving history.
        </p>
        <button type="button" class="btn btn-outline !text-sm mt-4" wire:click="exportData" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="exportData">Download my data (JSON)</span>
            <span wire:loading wire:target="exportData">Preparing download…</span>
        </button>
    </div>

    <div>
        <h3 class="text-base font-semibold text-ink">Marketing & parish updates</h3>
        <p class="mt-2 text-sm text-ink-muted">
            Optional emails about parish news and events. This does not affect essential account or pastoral communications related to your membership.
        </p>
        <form wire:submit="saveMarketingConsent" class="mt-4 space-y-4">
            <label class="member-consent-label">
                <input type="checkbox" wire:model="marketing_consent" class="member-consent-checkbox">
                <span>Send me occasional parish news and event updates by email</span>
            </label>
            <button type="submit" class="btn btn-outline !text-sm" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveMarketingConsent">Save preferences</span>
                <span wire:loading wire:target="saveMarketingConsent">Saving…</span>
            </button>
        </form>
    </div>

    <div>
        <h3 class="text-base font-semibold text-ink">Request account deletion</h3>
        <p class="mt-2 text-sm text-ink-muted">
            You may ask us to delete your personal data. Some giving records may be retained in anonymised form where UK charity law requires it. See our
            <a href="{{ $privacyPolicyUrl }}" class="text-brand hover:underline">Privacy Policy</a> for details.
        </p>
        @if ($erasureRequested)
            <p class="mt-4 text-sm text-amber-800 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3" role="status">
                A deletion request is on file for your account. The parish office will process it shortly. You can also email
                <a href="mailto:{{ $dataProtectionEmail }}" class="text-brand hover:underline">{{ $dataProtectionEmail }}</a>.
            </p>
        @else
            <button
                type="button"
                class="btn btn-outline !text-sm mt-4 !border-red-200 !text-red-700 hover:!bg-red-50"
                wire:click="requestErasure"
                wire:confirm="Request deletion of your parish account data? You will remain signed in until the parish processes your request."
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="requestErasure">Request account deletion</span>
                <span wire:loading wire:target="requestErasure">Submitting…</span>
            </button>
        @endif
    </div>

    <p class="text-xs text-ink-muted">
        Questions about data protection? Contact
        <a href="mailto:{{ $dataProtectionEmail }}" class="text-brand hover:underline">{{ $dataProtectionEmail }}</a>
        or learn about your rights from the
        <a href="{{ $icoComplaintUrl }}" target="_blank" rel="noopener noreferrer" class="text-brand hover:underline">Information Commissioner's Office (ICO)</a>.
    </p>
</div>
