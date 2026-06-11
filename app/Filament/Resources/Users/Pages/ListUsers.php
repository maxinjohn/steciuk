<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All users')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query),
            'members' => Tab::make('Parish members')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('role', UserRole::Member->value)),
            'panel_members' => Tab::make('Panel members')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas('panels')),
            'staff' => Tab::make('Admin & staff')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('role', UserRole::panelRoleSlugs())),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => auth()->user()?->can('create', User::class) ?? false),
        ];
    }
}
