<?php

namespace App\Enums;

use Filament\Support\Contracts\Collapsible;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum AdminNavigationGroup: string implements Collapsible, HasIcon, HasLabel
{
    case Overview = 'overview';
    case Website = 'website';
    case Worship = 'worship';
    case Media = 'media';
    case Messages = 'messages';
    case SiteSettings = 'site_settings';
    case TeamSecurity = 'team_security';

    public function getLabel(): string
    {
        return match ($this) {
            self::Overview => 'Overview',
            self::Website => 'Website Content',
            self::Worship => 'Worship & Parish',
            self::Media => 'Photos & Media',
            self::Messages => 'Messages & Forms',
            self::SiteSettings => 'Site Settings',
            self::TeamSecurity => 'Team & Security',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Overview => Heroicon::OutlinedHomeModern,
            self::Website => Heroicon::OutlinedDocumentText,
            self::Worship => Heroicon::OutlinedBuildingLibrary,
            self::Media => Heroicon::OutlinedPhoto,
            self::Messages => Heroicon::OutlinedInbox,
            self::SiteSettings => Heroicon::OutlinedCog6Tooth,
            self::TeamSecurity => Heroicon::OutlinedShieldCheck,
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
