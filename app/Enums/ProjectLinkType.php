<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectLinkType: string implements HasLabel
{
    case Web = 'web';
    case Github = 'github';
    case Playstore = 'playstore';
    case Download = 'download';

    public function getLabel(): string
    {
        return match ($this) {
            self::Web => 'Web / Landing Page',
            self::Github => 'GitHub',
            self::Playstore => 'Play Store',
            self::Download => 'Unduh / Download',
        };
    }

    public function defaultPublicLabel(): string
    {
        return match ($this) {
            self::Web => 'Website',
            self::Github => 'GitHub',
            self::Playstore => 'Play Store',
            self::Download => 'Unduh',
        };
    }
}
