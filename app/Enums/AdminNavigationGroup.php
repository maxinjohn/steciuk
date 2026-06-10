<?php

namespace App\Enums;

use Filament\Support\Contracts\Collapsible;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum AdminNavigationGroup: string implements Collapsible, HasIcon, HasLabel
{
    case Overview = 'overview';
    case People = 'people';
    case Giving = 'giving';
    case Messages = 'messages';
    case Website = 'website';
    case Worship = 'worship';
    case Media = 'media';
    case SiteSettings = 'site_settings';
    case Security = 'security';

    public function getLabel(): string
    {
        return match ($this) {
            self::Overview => 'Overview',
            self::People => 'People & households',
            self::Giving => 'Giving & donations',
            self::Messages => 'Messages & forms',
            self::Website => 'Website content',
            self::Worship => 'Worship & parish',
            self::Media => 'Photos & media',
            self::SiteSettings => 'Site settings',
            self::Security => 'Security & access',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Overview => Heroicon::OutlinedHomeModern,
            self::People => Heroicon::OutlinedUserGroup,
            self::Giving => Heroicon::OutlinedBanknotes,
            self::Messages => Heroicon::OutlinedInbox,
            self::Website => Heroicon::OutlinedDocumentText,
            self::Worship => Heroicon::OutlinedBuildingLibrary,
            self::Media => Heroicon::OutlinedPhoto,
            self::SiteSettings => Heroicon::OutlinedCog6Tooth,
            self::Security => Heroicon::OutlinedShieldCheck,
        };
    }

    public function isCollapsible(): bool
    {
        return true;
    }

    public function isCollapsed(): bool
    {
        return true;
    }
}
