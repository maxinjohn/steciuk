<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;

class CompactTableActions
{
    public static function editButton(?string $tooltip = 'Edit'): EditAction
    {
        return EditAction::make()
            ->iconButton()
            ->tooltip($tooltip);
    }

    public static function viewButton(?string $tooltip = 'View'): ViewAction
    {
        return ViewAction::make()
            ->iconButton()
            ->tooltip($tooltip);
    }

    /**
     * @param  array<Action|ActionGroup>  $actions
     */
    public static function overflowMenu(array $actions, ?string $tooltip = 'More actions'): ActionGroup
    {
        return ActionGroup::make($actions)
            ->icon(Heroicon::EllipsisVertical)
            ->iconButton()
            ->tooltip($tooltip)
            ->dropdownPlacement('bottom-end');
    }

    /**
     * @param  array<Action|ActionGroup>  $menuActions
     * @return array<EditAction|ActionGroup>
     */
    public static function editWithMenu(array $menuActions): array
    {
        if ($menuActions === []) {
            return [self::editButton()];
        }

        return [
            self::editButton(),
            self::overflowMenu($menuActions),
        ];
    }

    /**
     * @param  (callable(mixed): bool)|null  $deleteVisible
     * @return array<EditAction|ActionGroup>
     */
    public static function editWithDelete(?callable $deleteVisible = null, ?callable $beforeDelete = null): array
    {
        $delete = DeleteAction::make();

        if ($deleteVisible !== null) {
            $delete->visible($deleteVisible);
        }

        if ($beforeDelete !== null) {
            $delete->before($beforeDelete);
        }

        return self::editWithMenu([$delete]);
    }
}
