<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AccountStatus;
use App\Filament\Resources\Families\FamilyResource;
use App\Models\Role;
use App\Models\User;
use App\Services\DonationService;
use App\Support\ParishPronouns;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('First name'),
                        TextEntry::make('last_name')
                            ->label('Last name'),
                        TextEntry::make('pronouns')
                            ->label('Pronouns')
                            ->formatStateUsing(fn (?string $state): string => ParishPronouns::label($state) ?? ($state ?: '—')),
                        TextEntry::make('gender')
                            ->label('Gender')
                            ->formatStateUsing(fn (?string $state, User $record): string => $record->formattedGender() ?? '—'),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->copyable()
                            ->placeholder('No email'),
                        TextEntry::make('phone')
                            ->placeholder('—'),
                        TextEntry::make('date_of_birth')
                            ->label('Date of birth')
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('preferred_worship_location')
                            ->label('Preferred worship location')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('formatted_address')
                            ->label('Address')
                            ->state(fn (User $record): string => $record->formattedAddress() ?: '—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Account & access')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('role')
                            ->formatStateUsing(fn (?string $state): string => Role::labelForSlug($state))
                            ->badge(),
                        TextEntry::make('designation.name')
                            ->label('Designation')
                            ->placeholder('—'),
                        TextEntry::make('panels.name')
                            ->label('Panels')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('signature_status')
                            ->label('Verification signature')
                            ->state(fn (User $record): string => $record->canUploadVerificationSignature()
                                ? ($record->hasUploadedSignature() ? 'Uploaded' : 'Not uploaded')
                                : '—')
                            ->visible(fn (User $record): bool => $record->canUploadVerificationSignature()),
                        TextEntry::make('account_status')
                            ->label('Approval')
                            ->formatStateUsing(fn (?string $state): string => AccountStatus::tryFrom((string) $state)?->label() ?? 'Unknown')
                            ->badge(),
                        TextEntry::make('is_active')
                            ->label('Active')
                            ->formatStateUsing(fn (?bool $state): string => $state ? 'Yes' : 'No')
                            ->badge()
                            ->color(fn (?bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('created_at')
                            ->label('Registered')
                            ->dateTime(),
                        TextEntry::make('approved_at')
                            ->label('Approved')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('approvedBy.name')
                            ->label('Approved by')
                            ->formatStateUsing(fn (?string $state, User $record): string => $record->approvedBy?->displayFullName() ?? '—')
                            ->placeholder('—'),
                    ]),
                Section::make('Household')
                    ->columns(3)
                    ->visible(fn (User $record): bool => $record->canBelongToHousehold())
                    ->schema([
                        TextEntry::make('family.name')
                            ->label('Family household')
                            ->formatStateUsing(fn (?string $state, User $record): string => $record->family?->adminDisplayLabel() ?? 'Not linked to a household')
                            ->url(fn (User $record): ?string => $record->family_id
                                ? FamilyResource::getUrl('edit', ['record' => $record->family_id])
                                : null),
                        TextEntry::make('family_relationship')
                            ->label('Relationship')
                            ->formatStateUsing(fn (?string $state, User $record): string => $record->familyRelationship()?->label() ?? '—')
                            ->placeholder('—'),
                        TextEntry::make('is_family_admin')
                            ->label('Family administrator')
                            ->formatStateUsing(fn (?bool $state): string => $state ? 'Yes' : 'No'),
                        TextEntry::make('household_size')
                            ->label('Members in household')
                            ->state(fn (User $record): string => $record->family_id
                                ? (string) $record->family?->membersCount()
                                : '—'),
                        TextEntry::make('family.activeMembersCount')
                            ->label('Active household members')
                            ->state(fn (User $record): string => $record->family_id
                                ? (string) $record->family?->activeMembersCount()
                                : '—'),
                        TextEntry::make('family.is_active')
                            ->label('Household active')
                            ->formatStateUsing(fn ($state, User $record): string => $record->family
                                ? ($record->family->isActive() ? 'Yes' : 'No')
                                : '—'),
                        TextEntry::make('household_members')
                            ->label('Household members')
                            ->columnSpanFull()
                            ->visible(fn (User $record): bool => $record->family_id !== null)
                            ->formatStateUsing(function (User $record): string {
                                $members = $record->family?->members()
                                    ->orderBy('first_name')
                                    ->orderBy('last_name')
                                    ->get() ?? collect();

                                if ($members->isEmpty()) {
                                    return 'No members linked yet.';
                                }

                                return $members->map(function (User $member): string {
                                    $name = trim($member->displayFirstName().' '.$member->displayLastName());
                                    $relationship = $member->familyRelationship()?->label() ?? 'Member';
                                    $email = $member->email ?? 'no email';
                                    $status = $member->isActive() ? 'active' : 'inactive';

                                    return "{$name} · {$relationship} · {$email} · {$status}";
                                })->implode("\n");
                            })
                            ->listWithLineBreaks(),
                    ]),
                Section::make('Giving summary')
                    ->columns(2)
                    ->visible(fn (User $record): bool => $record->isMember())
                    ->schema([
                        TextEntry::make('personal_giving')
                            ->label('Approved personal giving')
                            ->state(fn (User $record): string => '£'.number_format(app(DonationService::class)->approvedTotalForUser($record), 2)),
                        TextEntry::make('household_giving')
                            ->label('Approved household giving')
                            ->state(fn (User $record): string => $record->family_id
                                ? '£'.number_format(app(DonationService::class)->approvedTotalForFamily($record->family_id), 2)
                                : '—'),
                    ]),
            ]);
    }
}
