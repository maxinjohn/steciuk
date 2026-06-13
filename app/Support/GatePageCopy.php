<?php

namespace App\Support;

final class GatePageCopy
{
    public const LAUNCH_TITLE = 'Opening soon';

    public const LAUNCH_SUBTITLE_COUNTDOWN = 'Pre-launch';

    public const LAUNCH_SUBTITLE_RIBBON = 'Opening ceremony';

    public const LAUNCH_MESSAGE_COUNTDOWN = 'This section of our parish website is not live yet. Please return when the countdown ends.';

    public const LAUNCH_MESSAGE_RIBBON = 'This page will open when a parish team member cuts the ribbon.';

    public const MAINTENANCE_BADGE = 'Under maintenance';

    public const MAINTENANCE_TITLE = 'We\'ll be right back';

    public const MAINTENANCE_MESSAGE = 'We are carrying out scheduled maintenance on this website. Worship and parish life continue as usual. Please check back soon.';

    /**
     * @return list<string>
     */
    public static function maintenanceChips(): array
    {
        return [
            'Worship continues',
            'Parish life continues',
            'Check back soon',
        ];
    }

    /**
     * @return list<array{label: string}>
     */
    public static function maintenanceChipRows(): array
    {
        return array_map(
            fn (string $label): array => ['label' => $label],
            self::maintenanceChips(),
        );
    }
}
