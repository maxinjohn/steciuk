<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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

    public function isViewer(): bool
    {
        return $this->roleSlug() === UserRole::Viewer->value;
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
        if ($this->isSuperAdmin()) {
            return true;
        }

        return app(\App\Services\PermissionService::class)->canAccessAdmin($this);
    }

    public function hasAdminPermission(\App\Enums\AdminPermission|string $permission): bool
    {
        return app(\App\Services\PermissionService::class)->can($this, $permission);
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
}
