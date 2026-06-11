<div class="member-portal-panel-stack">
    <div class="member-portal-card">
        <h2 class="member-portal-panel-title">Your giving summary</h2>
        <div class="member-giving-stats">
            <div class="member-giving-stat">
                <p class="member-giving-stat-label">Your approved giving</p>
                <p class="member-giving-stat-value">£{{ number_format($summary['personal'], 2) }}</p>
            </div>
            @if ($canViewHousehold)
                <div class="member-giving-stat">
                    <p class="member-giving-stat-label">Household approved giving</p>
                    <p class="member-giving-stat-value">£{{ number_format($summary['household'], 2) }}</p>
                </div>
            @endif
            @if ($summary['pending_count'] > 0)
                <div class="member-giving-stat member-giving-stat--pending">
                    <p class="member-giving-stat-label">Awaiting verification</p>
                    <p class="member-giving-stat-value">{{ $summary['pending_count'] }} {{ str('entry')->plural($summary['pending_count']) }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="member-portal-card">
        @if ($submitted)
            <div class="member-alert member-alert--success mb-5" role="status">
                Thank you. Your giving record was submitted and is pending parish verification.
            </div>
        @endif

        <h2 class="member-portal-panel-title">Report a donation</h2>
        <p class="member-portal-panel-intro">
            Tell us about a gift you have already made (for example by bank transfer). A parish admin will verify it before it appears in your approved total.
        </p>

        @if (filled($givingBankDetails['bank_name'] ?? null) || filled($givingBankDetails['account_number'] ?? null))
            <div class="member-giving-bank mt-5 rounded-2xl border border-[var(--site-border)] bg-[var(--site-surface)] p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-muted">Parish bank details</h3>
                <dl class="mt-3 grid gap-2 text-sm text-ink sm:grid-cols-2">
                    @if (filled($givingBankDetails['bank_name']))
                        <div><dt class="text-ink-muted">Bank</dt><dd class="font-medium">{{ $givingBankDetails['bank_name'] }}</dd></div>
                    @endif
                    @if (filled($givingBankDetails['account_name']))
                        <div><dt class="text-ink-muted">Account name</dt><dd class="font-medium">{{ $givingBankDetails['account_name'] }}</dd></div>
                    @endif
                    @if (filled($givingBankDetails['sort_code']))
                        <div><dt class="text-ink-muted">Sort code</dt><dd class="font-medium">{{ $givingBankDetails['sort_code'] }}</dd></div>
                    @endif
                    @if (filled($givingBankDetails['account_number']))
                        <div><dt class="text-ink-muted">Account number</dt><dd class="font-medium">{{ $givingBankDetails['account_number'] }}</dd></div>
                    @endif
                    @if (filled($givingBankDetails['reference']))
                        <div class="sm:col-span-2"><dt class="text-ink-muted">Reference</dt><dd class="font-medium">{{ $givingBankDetails['reference'] }}</dd></div>
                    @endif
                </dl>
                @if (filled($givingBankDetails['payment_link']))
                    <a href="{{ $givingBankDetails['payment_link'] }}" class="btn btn-secondary mt-4 inline-flex" target="_blank" rel="noopener noreferrer">Pay online</a>
                @endif
            </div>
        @endif

        <form wire:submit="submit" class="mt-6 space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="donation-amount" class="form-label">Amount (GBP) <span class="text-red-600">*</span></label>
                    <input id="donation-amount" type="number" step="0.01" min="0.01" wire:model.blur="amount" class="form-input" required @error('amount') aria-invalid="true" @enderror>
                    @error('amount')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="donation-method" class="form-label">How you gave <span class="text-red-600">*</span></label>
                    <select id="donation-method" wire:model.blur="method" class="form-input" required @error('method') aria-invalid="true" @enderror>
                        @foreach ($methodOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('method')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="donation-date" class="form-label">Date of gift <span class="text-red-600">*</span></label>
                    <input id="donation-date" type="date" wire:model.blur="donated_on" class="form-input" required @error('donated_on') aria-invalid="true" @enderror>
                    @error('donated_on')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="donation-reference" class="form-label">Bank reference / receipt no.</label>
                    <input id="donation-reference" type="text" wire:model.blur="reference" class="form-input" placeholder="Optional">
                    @error('reference')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="donation-note" class="form-label">Note for the parish office</label>
                <textarea id="donation-note" wire:model.blur="member_note" class="form-input min-h-24" placeholder="Optional — e.g. Gift Aid, project, or dedication"></textarea>
                @error('member_note')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                <label class="member-consent-label">
                    <input type="checkbox" wire:model="confirm_accuracy" class="member-consent-checkbox" @error('confirm_accuracy') aria-invalid="true" @enderror>
                    <span>I confirm the details above are accurate. Giving records are processed to administer parish finances and meet charity reporting obligations, as explained in our <a href="{{ $privacyPolicyUrl }}" target="_blank" rel="noopener noreferrer" class="text-brand hover:underline">Privacy Policy</a>. <span class="text-red-600">*</span></span>
                </label>
                @error('confirm_accuracy')<p class="form-error mt-2" role="alert">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit">Submit for verification</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>
        </form>
    </div>

    <div class="member-portal-card">
        <h2 class="member-portal-panel-title">Your giving history</h2>
        <div class="member-giving-list mt-5">
            @forelse ($donations as $donation)
                <div class="member-giving-item" wire:key="donation-{{ $donation->id }}">
                    <div>
                        <p class="font-semibold text-ink">{{ $donation->formattedAmount() }}</p>
                        <p class="text-sm text-ink-muted">
                            {{ $donation->methodEnum()?->label() ?? $donation->method }}
                            · {{ $donation->donated_on->format('j M Y') }}
                            @if ($donation->reference)
                                · Ref {{ $donation->reference }}
                            @endif
                        </p>
                        @if ($donation->member_note)
                            <p class="mt-1 text-sm text-ink-muted">{{ $donation->member_note }}</p>
                        @endif
                    </div>
                    <span @class([
                        'member-status-badge',
                        'member-status-badge--approved' => $donation->isApproved(),
                        'member-status-badge--pending' => $donation->isPending(),
                        'member-status-badge--rejected' => $donation->statusEnum() === \App\Enums\DonationStatus::Rejected,
                    ])>{{ $donation->statusEnum()->label() }}</span>
                </div>
            @empty
                <p class="text-sm text-ink-muted">No giving records yet.</p>
            @endforelse
        </div>
    </div>

    @if ($canViewHousehold && $householdDonations->isNotEmpty())
        <div class="member-portal-card">
            <h2 class="member-portal-panel-title">Household giving history</h2>
            <p class="member-portal-panel-intro">Gifts reported by other members of your household.</p>
            <div class="member-giving-list mt-5">
                @foreach ($householdDonations as $donation)
                    <div class="member-giving-item" wire:key="household-donation-{{ $donation->id }}">
                        <div>
                            <p class="font-semibold text-ink">{{ $donation->formattedAmount() }}</p>
                            <p class="text-sm text-ink-muted">
                                {{ $donation->user?->displayFullName() }}
                                · {{ $donation->methodEnum()?->label() ?? $donation->method }}
                                · {{ $donation->donated_on->format('j M Y') }}
                            </p>
                        </div>
                        <span @class([
                            'member-status-badge',
                            'member-status-badge--approved' => $donation->isApproved(),
                            'member-status-badge--pending' => $donation->isPending(),
                            'member-status-badge--rejected' => $donation->statusEnum() === \App\Enums\DonationStatus::Rejected,
                        ])>{{ $donation->statusEnum()->label() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="member-portal-card">
        <h2 class="member-portal-panel-title">Download giving statement (PDF)</h2>
        <p class="member-portal-panel-intro">
            Export approved giving for a calendar month or custom date range. By default the current month is selected below.
        </p>

        <div class="mt-6 space-y-5">
            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label for="export-month" class="form-label">Single month</label>
                    <input id="export-month" type="month" wire:model.live="export_month" class="form-input">
                    <p class="mt-1 text-xs text-ink-muted">Optional — overrides the date range below.</p>
                </div>
                <div>
                    <label for="export-from" class="form-label">From date</label>
                    <input id="export-from" type="date" wire:model.blur="export_from" class="form-input" @disabled(filled($export_month)) @error('export_from') aria-invalid="true" @enderror>
                    @error('export_from')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="export-to" class="form-label">To date</label>
                    <input id="export-to" type="date" wire:model.blur="export_to" class="form-input" @disabled(filled($export_month)) @error('export_to') aria-invalid="true" @enderror>
                    @error('export_to')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>

            <label class="member-consent-label">
                <input type="checkbox" wire:model="export_include_all_statuses" class="member-consent-checkbox">
                <span>Include pending and rejected entries (not just approved gifts)</span>
            </label>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="btn btn-outline !text-sm" wire:click="exportPersonalStatement" wire:loading.attr="disabled" wire:target="exportPersonalStatement">
                    <span wire:loading.remove wire:target="exportPersonalStatement">Download my giving PDF</span>
                    <span wire:loading wire:target="exportPersonalStatement">Preparing PDF…</span>
                </button>
                @if ($canViewHousehold)
                    <button type="button" class="btn btn-outline !text-sm" wire:click="exportHouseholdStatement" wire:loading.attr="disabled" wire:target="exportHouseholdStatement">
                        <span wire:loading.remove wire:target="exportHouseholdStatement">Download household giving PDF</span>
                        <span wire:loading wire:target="exportHouseholdStatement">Preparing PDF…</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
