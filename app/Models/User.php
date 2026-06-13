<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AdminPermission;
use App\Enums\FamilyRelationship;
use App\Enums\UserRole;
use App\Services\PermissionService;
use App\Support\ParishGender;
use App\Support\ParishPronouns;
use App\Support\UkAddressFormatter;
use App\Support\UserName;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'name',
    'first_name',
    'last_name',
    'pronouns',
    'gender',
    'email',
    'password',
    'role',
    'designation_id',
    'account_status',
    'is_active',
    'family_id',
    'is_family_admin',
    'family_relationship',
    'approved_at',
    'approved_by',
    'privacy_policy_accepted_at',
    'privacy_policy_version',
    'terms_accepted_at',
    'household_data_consent_at',
    'marketing_consent',
    'marketing_consent_at',
    'erasure_requested_at',
    'anonymized_at',
    'phone',
    'date_of_birth',
    'address_line_1',
    'address_line_2',
    'city',
    'county',
    'postcode',
    'preferred_worship_location',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;
    use InteractsWithMedia;

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (! Schema::hasColumn($user->getTable(), 'first_name')) {
                return;
            }

            if ($user->isDirty(['first_name', 'last_name']) && ! $user->isDirty('name')) {
                $user->name = UserName::fromParts($user->first_name, $user->last_name);
            } elseif ($user->isDirty('name') && ! $user->isDirty(['first_name', 'last_name'])) {
                $parts = UserName::split($user->name);
                $user->first_name = $parts['first_name'];
                $user->last_name = $parts['last_name'];
            } elseif (! $user->exists && blank($user->first_name) && filled($user->name)) {
                $parts = UserName::split($user->name);
                $user->first_name = $parts['first_name'];
                $user->last_name = $parts['last_name'];
            }

            if ($user->isDirty('role')) {
                $user->enforceRoleAssignmentPolicy();
            }

            if ($user->isDirty('email')) {
                \App\Support\Gravatar::forgetCache($user->getOriginal('email'));
            }
        });

        static::saved(function (User $user): void {
            if ($user->wasChanged('email')) {
                \App\Support\Gravatar::forgetCache($user->email);
            }
        });
    }

    private function enforceRoleAssignmentPolicy(): void
    {
        $actor = auth()->user();

        if (! $actor instanceof self) {
            return;
        }

        $requested = $this->roleSlug();
        $original = $this->getOriginal('role');
        $current = $original instanceof UserRole
            ? $original->value
            : (string) ($original ?? UserRole::Member->value);

        if ($current === UserRole::SuperAdmin->value && ! $actor->isSuperAdmin()) {
            $this->role = $current;

            return;
        }

        if (! $actor->canChangeRoleOf($this)) {
            $this->role = $this->exists ? $current : UserRole::Member->value;

            return;
        }

        if ($actor->canAssignRole($requested)) {
            return;
        }

        $this->role = $this->exists ? $current : UserRole::Member->value;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'approved_at' => 'datetime',
            'privacy_policy_accepted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'household_data_consent_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'marketing_consent_at' => 'datetime',
            'erasure_requested_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'is_family_admin' => 'boolean',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function accountStatus(): AccountStatus
    {
        $status = $this->account_status;

        if ($status instanceof AccountStatus) {
            return $status;
        }

        return AccountStatus::tryFrom((string) $status) ?? AccountStatus::Approved;
    }

    public function isAccountApproved(): bool
    {
        if (! $this->isMember()) {
            return true;
        }

        return $this->accountStatus() === AccountStatus::Approved;
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function familyIsActive(): bool
    {
        if (! $this->family_id) {
            return true;
        }

        return $this->family?->isActive() ?? true;
    }

    public function canUseMemberAccount(): bool
    {
        return $this->isMember()
            && $this->isActive()
            && $this->familyIsActive()
            && $this->isAccountApproved();
    }

    public function memberAccessBlockReason(): ?string
    {
        if (! $this->familyIsActive()) {
            return 'Your parish family account has been deactivated. Please contact the parish office for help.';
        }

        if (! $this->isActive()) {
            return 'Your parish account has been deactivated. Please contact the parish office for help.';
        }

        if (! $this->isMember()) {
            return null;
        }

        return match ($this->accountStatus()) {
            AccountStatus::Pending => 'Your parish account is awaiting approval.',
            AccountStatus::Rejected => 'Your registration was not approved. Please contact the parish office if you need assistance.',
            default => null,
        };
    }

    public function isLinkedHouseholdMember(): bool
    {
        return $this->isMember()
            && $this->family_id !== null
            && ! $this->isFamilyAdmin();
    }

    public function householdMemberPortalMessage(): string
    {
        $this->loadMissing('family');

        $familyName = $this->family?->memberPortalLabel() ?? 'your parish household';

        return "An account with this email is already linked to {$familyName}. Please sign in instead of registering again.";
    }

    public function householdMemberRegistrationMessage(): string
    {
        $this->loadMissing('family');

        $familyName = $this->family?->memberPortalLabel() ?? 'your parish household';

        return "An account with this email is already linked to {$familyName}. Please sign in instead of creating a new registration.";
    }

    public function isAccountPending(): bool
    {
        return $this->isMember() && $this->accountStatus() === AccountStatus::Pending;
    }

    public function isFamilyAdmin(): bool
    {
        return (bool) $this->is_family_admin;
    }

    /**
     * Parish accounts that may be linked to a household (members and team roles).
     */
    public function canBelongToHousehold(): bool
    {
        return in_array($this->roleSlug(), [
            UserRole::Member->value,
            UserRole::Editor->value,
            UserRole::Admin->value,
            UserRole::SuperAdmin->value,
        ], true);
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeHouseholdEligible(Builder $query): Builder
    {
        return $query->whereIn('role', [
            UserRole::Member->value,
            UserRole::Editor->value,
            UserRole::Admin->value,
            UserRole::SuperAdmin->value,
        ]);
    }

    public function isLinkedToHousehold(): bool
    {
        return $this->family_id !== null;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    public function hasErasureRequest(): bool
    {
        return $this->erasure_requested_at !== null && ! $this->isAnonymized();
    }

    public function canManageHouseholdOnPortal(): bool
    {
        return $this->isFamilyAdmin()
            && $this->family_id !== null
            && $this->isActive()
            && $this->familyIsActive()
            && $this->isAccountApproved();
    }

    public function canViewHouseholdGivingOnPortal(): bool
    {
        return $this->family_id !== null
            && $this->isActive()
            && $this->familyIsActive()
            && $this->isAccountApproved();
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')->singleFile();
        $this->addMediaCollection('signature')->singleFile();
    }

    public function initials(): string
    {
        $first = trim((string) ($this->first_name ?: UserName::split($this->name)['first_name']));
        $last = trim((string) ($this->last_name ?: UserName::split($this->name)['last_name']));

        if ($first !== '' && $last !== '') {
            return strtoupper(substr($first, 0, 1).substr($last, 0, 1));
        }

        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn (string $part) => substr($part, 0, 1))->implode(''));
    }

    public function displayFirstName(): string
    {
        return trim((string) ($this->first_name ?: UserName::split($this->name)['first_name'] ?: $this->name));
    }

    public function displayLastName(): string
    {
        return trim((string) ($this->last_name ?: UserName::split($this->name)['last_name']));
    }

    public function displayFullName(): string
    {
        $full = trim($this->displayFirstName().' '.$this->displayLastName());

        return $full !== '' ? $full : trim((string) $this->name);
    }

    public function formattedPronouns(): ?string
    {
        return ParishPronouns::label($this->pronouns) ?? (filled($this->pronouns) ? trim((string) $this->pronouns) : null);
    }

    public function formattedGender(): ?string
    {
        return ParishGender::label($this->gender) ?? (filled($this->gender) ? trim((string) $this->gender) : null);
    }

    public function familyRelationship(): ?FamilyRelationship
    {
        return FamilyRelationship::tryFromValue($this->family_relationship);
    }

    public function requiresUniqueEmail(): bool
    {
        if (! $this->isMember()) {
            return filled($this->email);
        }

        if ($this->isFamilyAdmin() || $this->familyRelationship() === FamilyRelationship::Head) {
            return true;
        }

        if ($this->family_id === null) {
            return true;
        }

        return false;
    }

    public function householdMemberEmailIsOptional(): bool
    {
        if ($this->family_id === null || $this->isFamilyAdmin()) {
            return false;
        }

        return $this->familyRelationship()?->emailIsOptionalForHouseholdMember() ?? true;
    }

    public function canSignInToMemberPortal(): bool
    {
        return $this->isMember()
            && $this->isAccountApproved()
            && filled($this->email)
            && $this->isActive()
            && $this->familyIsActive();
    }

    public function hasUploadedProfilePhoto(): bool
    {
        return $this->hasMedia('profile_photo');
    }

    public function profilePhotoUrl(): ?string
    {
        $media = $this->getFirstMedia('profile_photo');

        return $media?->getUrl();
    }

    public function gravatarUrl(int $size = 256): ?string
    {
        if (! filled($this->email)) {
            return null;
        }

        return \App\Support\Gravatar::url((string) $this->email, $size);
    }

    public function hasGravatar(int $size = 256): bool
    {
        return filled($this->email)
            && \App\Support\Gravatar::exists((string) $this->email, $size);
    }

    public function avatarUrl(int $size = 256): ?string
    {
        if ($url = $this->profilePhotoUrl()) {
            return $url;
        }

        return $this->hasGravatar($size) ? $this->gravatarUrl($size) : null;
    }

    public function usesGravatar(): bool
    {
        return ! $this->hasUploadedProfilePhoto() && $this->hasGravatar();
    }

    public function roleSlug(): string
    {
        $role = $this->role;

        if ($role instanceof UserRole) {
            return $role->value;
        }

        return (string) $role;
    }

    public function roleRecord(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function panels(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Panel::class)
            ->withPivot(['sort_order', 'notes'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function isSuperAdmin(): bool
    {
        if ($this->roleSlug() === UserRole::SuperAdmin->value) {
            return true;
        }

        return (bool) Role::findBySlug($this->roleSlug())?->grants_full_access;
    }

    public function isEditor(): bool
    {
        return $this->roleSlug() === UserRole::Editor->value;
    }

    public function isAdmin(): bool
    {
        return $this->roleSlug() === UserRole::Admin->value;
    }

    public function isVicar(): bool
    {
        return $this->roleSlug() === UserRole::Vicar->value;
    }

    public function canUploadVerificationSignature(): bool
    {
        return $this->isVicar();
    }

    public function hasUploadedSignature(): bool
    {
        return $this->hasMedia('signature');
    }

    public function signatureUrl(): ?string
    {
        $media = $this->getFirstMedia('signature');

        return $media?->getUrl();
    }

    public function designationLabel(): ?string
    {
        return $this->designation?->name;
    }

    public function isMember(): bool
    {
        return $this->roleSlug() === UserRole::Member->value;
    }

    public function hasFullPanelAccess(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isVicar();
    }

    public function canPublishContent(): bool
    {
        return $this->hasFullPanelAccess();
    }

    public function canManageSuperAdminAccount(User $target): bool
    {
        if (! $target->isSuperAdmin()) {
            return true;
        }

        return $this->isSuperAdmin();
    }

    public function canAssignRole(string $roleSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $roleSlug !== UserRole::SuperAdmin->value;
        }

        return false;
    }

    public function canChangeRoleOf(User $target): bool
    {
        if (! $this->canManageTeamRoles()) {
            return false;
        }

        if ((int) $this->id === (int) $target->id) {
            return false;
        }

        return $this->canManageSuperAdminAccount($target);
    }

    public function resolveRoleForCreate(string $requested): string
    {
        if (! $this->canManageTeamRoles() || ! $this->canAssignRole($requested)) {
            return UserRole::Member->value;
        }

        $allowed = array_keys($this->assignableRoleOptions());

        if ($allowed !== [] && in_array($requested, $allowed, true)) {
            return $requested;
        }

        return UserRole::Member->value;
    }

    public function resolveRoleForUpdate(string $requested, User $target): string
    {
        $current = $target->roleSlug();

        if (! $this->canChangeRoleOf($target)) {
            return $current;
        }

        if (! $this->canAssignRole($requested)) {
            return $current;
        }

        $allowed = array_keys($this->assignableRoleOptions());

        if ($allowed !== [] && in_array($requested, $allowed, true)) {
            return $requested;
        }

        return $current;
    }

    public function canManageTeamRoles(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * @return array<string, string>
     */
    public function assignableRoleOptions(): array
    {
        if ($this->isSuperAdmin()) {
            return Role::options();
        }

        if ($this->isAdmin()) {
            return collect(Role::options())
                ->reject(fn (string $label, string $slug): bool => $slug === UserRole::SuperAdmin->value)
                ->all();
        }

        return [];
    }

    public function formattedAddress(): string
    {
        return UkAddressFormatter::format(
            line1: $this->address_line_1,
            line2: $this->address_line_2,
            city: $this->city,
            county: $this->county,
            postcode: $this->postcode,
        );
    }

    public function hasRole(UserRole|string ...$roles): bool
    {
        $slugs = array_map(
            fn (UserRole|string $role) => $role instanceof UserRole ? $role->value : $role,
            $roles,
        );

        return in_array($this->roleSlug(), $slugs, true);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->isActive() || ! $this->familyIsActive()) {
            return false;
        }

        if (! $this->isAccountApproved() && $this->isMember()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return app(PermissionService::class)->canAccessAdmin($this);
    }

    public function hasAdminPermission(AdminPermission|string $permission): bool
    {
        return app(PermissionService::class)->can($this, $permission);
    }

    public function createdPages(): HasMany
    {
        return $this->hasMany(Page::class, 'created_by');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function createdNews(): HasMany
    {
        return $this->hasMany(News::class, 'created_by');
    }

    public function createdSermons(): HasMany
    {
        return $this->hasMany(Sermon::class, 'created_by');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
