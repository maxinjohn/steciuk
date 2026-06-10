<?php

namespace App\Models;

use App\Support\FamilyLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = [
        'name',
        'admin_user_id',
        'preferred_worship_location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function activeMembersCount(): int
    {
        return $this->members()->where('is_active', true)->count();
    }

    public function membersCount(): int
    {
        return $this->members()->count();
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function adminDisplayLabel(): string
    {
        return FamilyLabel::forAdmin($this);
    }

    public function memberPortalLabel(): string
    {
        return FamilyLabel::forMemberPortal($this);
    }
}
