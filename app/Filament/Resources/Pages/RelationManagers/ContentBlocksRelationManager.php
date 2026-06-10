<?php

namespace App\Filament\Resources\Pages\RelationManagers;

use App\Filament\Support\CompactTableActions;
use App\Filament\Support\ContentBlockFormBuilder;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class ContentBlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'contentBlocks';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Page Sections';

    protected static ?string $modelLabel = 'section';

    protected static ?string $pluralModelLabel = 'sections';

    public function form(Schema $schema): Schema
    {
        return $schema->components(ContentBlockFormBuilder::fields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? $state)
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('preview')
                    ->label('Preview')
                    ->state(function ($record): string {
                        $content = $record->content ?? [];

                        return $content['headline']
                            ?? $content['heading']
                            ?? $content['quote']
                            ?? $content['body']
                            ?? '—';
                    })
                    ->limit(60)
                    ->wrap(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Add section'),
            ])
            ->recordActions([
                CompactTableActions::editButton()
                    ->slideOver(),
                CompactTableActions::overflowMenu([
                    DeleteAction::make(),
                ]),
            ], RecordActionsPosition::AfterColumns)
            ->emptyStateHeading('No page sections yet')
            ->emptyStateDescription('Add hero banners, location tabs, CTAs, and other layout blocks that appear on the public page.');
    }
}
