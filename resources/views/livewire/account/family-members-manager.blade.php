<div class="member-portal-panel-stack">
    @if ($canCreateHousehold)
        <div class="member-portal-card">
            @if ($householdCreated)
                <div class="member-alert member-alert--success mb-5" role="status">
                    Your household is ready. You can now add family members below.
                </div>
            @endif

            <h2 class="member-portal-panel-title">Create family</h2>
            <p class="member-portal-panel-intro">
                Register one person at a time. When your account is approved, create your household here and add spouses, children, or other dependents from your profile.
            </p>

            <form wire:submit="createHousehold" class="mt-6 space-y-5">
                <div>
                    <label for="family-household-name" class="form-label">Family name <span class="text-ink-muted">(optional)</span></label>
                    <input id="family-household-name" type="text" wire:model.blur="family_name" class="form-input" placeholder="e.g. Thadathil" @error('family_name') aria-invalid="true" @enderror>
                    @error('family_name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    <p class="mt-2 text-xs text-ink-muted">Many families can share the same surname (for example “Thadathil”). This label helps your parish recognise your household — it does not need to be unique.</p>
                </div>
                <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="createHousehold">Create family</span>
                    <span wire:loading wire:target="createHousehold">Creating…</span>
                </button>
            </form>
        </div>
    @else
    <div class="member-portal-card">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="member-portal-panel-title">Family household</h2>
                <p class="member-portal-panel-intro">
                    @if ($canManage)
                        Add household members here. You are the primary family account and sign in on behalf of your household — other members do not need their own login. Each new person stays pending until a parish admin approves them.
                    @else
                        Members linked to your parish family profile.
                    @endif
                </p>
            </div>
        </div>

        @if ($emailUpdated)
            <div class="member-alert member-alert--success mt-5" role="status">
                Email saved for parish records. Household members sign in through the primary family account, not their own email.
            </div>
        @endif

        <div class="member-family-list mt-6">
            @forelse ($members as $member)
                <div class="member-family-item" wire:key="family-member-{{ $member->id }}">
                    <div class="member-family-item-main">
                        <x-member-avatar :user="$member" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-ink">{{ $member->displayFullName() }}</p>
                            <p class="text-sm text-ink-muted">
                                {{ \App\Enums\FamilyRelationship::options()[$member->family_relationship] ?? 'Family member' }}
                                @if ($member->email)
                                    · {{ $member->email }}
                                @else
                                    · No email yet
                                @endif
                            </p>

                            @if ($canManage && ! $member->isFamilyAdmin() && $editingMemberId === $member->id)
                                <form wire:submit="saveMemberEmail" class="member-family-email-form mt-3">
                                    <label for="edit-email-{{ $member->id }}" class="form-label">Email address</label>
                                    <div class="member-family-email-row">
                                        <input
                                            id="edit-email-{{ $member->id }}"
                                            type="email"
                                            wire:model.blur="editEmail"
                                            class="form-input"
                                            placeholder="Optional for children"
                                            @error('editEmail') aria-invalid="true" @enderror
                                        >
                                        <button type="submit" class="btn btn-primary !text-sm" wire:loading.attr="disabled">Save</button>
                                        <button type="button" class="btn btn-outline !text-sm" wire:click="cancelEditingEmail">Cancel</button>
                                    </div>
                                    <p class="mt-2 text-xs text-ink-muted">
                                        {{ $member->familyRelationship()?->emailHintForHouseholdMember() }}
                                    </p>
                                    @error('editEmail')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                                </form>
                            @elseif ($canManage && ! $member->isFamilyAdmin())
                                <button type="button" class="member-family-edit-link mt-2" wire:click="startEditingEmail({{ $member->id }})">
                                    {{ $member->email ? 'Update email' : 'Add email' }}
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span @class([
                            'member-status-badge',
                            'member-status-badge--approved' => $member->isAccountApproved(),
                            'member-status-badge--pending' => $member->isAccountPending(),
                            'member-status-badge--rejected' => $member->accountStatus() === \App\Enums\AccountStatus::Rejected,
                        ])>{{ $member->accountStatus()->label() }}</span>
                        @if ($canManage && ! $member->isFamilyAdmin() && $member->id !== auth()->id())
                            <button
                                type="button"
                                class="member-family-remove-link"
                                wire:click="removeMember({{ $member->id }})"
                                wire:confirm="Remove {{ $member->displayFullName() }} from your household? Their parish account will be kept if they have their own login."
                                wire:loading.attr="disabled"
                            >
                                Remove
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-muted">No household members are linked yet.</p>
            @endforelse
        </div>
    </div>
    @endif

    @if ($canManage)
        <div class="member-portal-card">
            @if ($saved)
                <div class="member-alert member-alert--success mb-5" role="status">
                    Household member submitted for parish approval.
                </div>
            @endif

            <h3 class="member-portal-panel-title">Add household member</h3>
            <p class="member-portal-panel-intro mt-2">
                Children and dependents do not need an email. Only the primary family account signs in to the member portal on behalf of your household.
            </p>
            <form wire:submit="addMember" class="mt-6 space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="family-member-first-name" class="form-label">First name <span class="text-red-600">*</span></label>
                        <input id="family-member-first-name" type="text" wire:model.blur="first_name" class="form-input" autocomplete="given-name" @error('first_name') aria-invalid="true" @enderror>
                        @error('first_name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="family-member-last-name" class="form-label">Last name</label>
                        <input id="family-member-last-name" type="text" wire:model.blur="last_name" class="form-input" autocomplete="family-name" @error('last_name') aria-invalid="true" @enderror>
                        @error('last_name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="family-member-pronouns" class="form-label">Pronouns</label>
                        <select id="family-member-pronouns" wire:model.blur="pronouns" class="form-input">
                            @foreach ($pronounOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('pronouns')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="family-member-gender" class="form-label">Gender</label>
                        <select id="family-member-gender" wire:model.blur="gender" class="form-input">
                            @foreach ($genderOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('gender')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="family-member-relationship" class="form-label">Relationship <span class="text-red-600">*</span></label>
                        <select id="family-member-relationship" wire:model.live="relationship" class="form-input">
                            @foreach ($relationshipOptions as $value => $label)
                                @if ($value !== 'head')
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="family-member-dob" class="form-label">Date of birth <span class="text-red-600">*</span></label>
                        <input id="family-member-dob" type="date" wire:model.blur="date_of_birth" class="form-input" required @error('date_of_birth') aria-invalid="true" @enderror>
                        @error('date_of_birth')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="family-member-email" class="form-label">Email <span class="text-ink-muted">(optional)</span></label>
                        <input id="family-member-email" type="email" wire:model.blur="email" class="form-input" placeholder="Leave blank for children without email" @error('email') aria-invalid="true" @enderror>
                        <p class="mt-2 text-xs text-ink-muted">
                            {{ \App\Enums\FamilyRelationship::tryFromValue($relationship)?->emailHintForHouseholdMember() }}
                        </p>
                        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="family-member-phone" class="form-label">Phone</label>
                        <input id="family-member-phone" type="tel" wire:model.blur="phone" class="form-input">
                    </div>
                </div>
                @if ($needsHouseholdConsent)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <label class="member-consent-label">
                            <input type="checkbox" wire:model="household_data_consent" class="member-consent-checkbox" @error('household_data_consent') aria-invalid="true" @enderror>
                            <span>I confirm I have authority to add this household member and consent to the parish processing their personal data as described in our <a href="{{ $privacyPolicyUrl }}" target="_blank" rel="noopener noreferrer" class="text-brand hover:underline">Privacy Policy</a>. <span class="text-red-600">*</span></span>
                        </label>
                        @error('household_data_consent')<p class="form-error mt-2" role="alert">{{ $message }}</p>@enderror
                    </div>
                @endif
                <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="addMember">Add household member</span>
                    <span wire:loading wire:target="addMember">Submitting…</span>
                </button>
            </form>
        </div>
    @endif
</div>
