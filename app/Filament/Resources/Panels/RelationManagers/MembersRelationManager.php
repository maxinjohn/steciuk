<?php

namespace App\Filament\Resources\Panels\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\PanelMemberOptions;
use App\Models\Panel;
use App\Models\User;
use App\Services\PanelMembershipService;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Panel members';

    public function table(Table $table): Table
    {
        /** @var Panel $panel */
        $panel = $this->getOwnerRecord();

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['designation']))
            ->defaultSort('last_name')
            ->emptyStateHeading('No panel members yet')
            ->emptyStateDescription('Add existing parish users to this panel. Every panel member must already have a site account.')
            ->headerActions([
                Action::make('addMember')
                    ->label('Add parish user')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (): bool => auth()->user()?->can('update', $panel) ?? false)
                    ->slideOver()
                    ->modalWidth(Width::TwoExtraLarge)
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalHeading('Add parish user to panel')
                    ->modalDescription('Choose an active site user who is not already listed on this panel.')
                    ->form([
                        Select::make('user_id')
                            ->label('Parish user')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => PanelMemberOptions::options($panel))
                            ->getSearchResultsUsing(fn (string $search): array => PanelMemberOptions::options($panel, $search))
                            ->getOptionLabelUsing(fn ($value): ?string => PanelMemberOptions::labelForId((int) $value))
                            ->helperText('Choose any active site user who is not already on this panel.'),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) use ($panel): void {
                        $member = User::query()->findOrFail((int) $data['user_id']);

                        app(PanelMembershipService::class)->attachMember(
                            $panel,
                            $member,
                            $data['notes'] ?? null,
                        );

                        Notification::make()
                            ->title('Panel member added')
                            ->body($member->displayFullName().' is listed on this panel and under Users → Panel members.')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('last_name')
                    ->label('Member')
                    ->searchable(['first_name', 'last_name', 'name', 'email'])
                    ->sortable(['last_name', 'first_name'])
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->displayFullName())
                    ->description(fn (User $record): ?string => $record->email),
                TextColumn::make('designation.name')
                    ->label('Designation')
                    ->placeholder('—'),
                TextColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->roleRecord?->name ?? $record->roleSlug()),
                TextColumn::make('pivot.notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Remove from panel'),
            ], RecordActionsPosition::AfterColumns)
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]));
    }
}
