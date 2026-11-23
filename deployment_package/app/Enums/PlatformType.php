<?php

declare(strict_types=1);

namespace App\Enums;

enum PlatformType: string
{
    case DESKTOP = 'desktop';
    case MOBILE = 'mobile';

    public function label(): string
    {
        return match ($this) {
            self::DESKTOP => 'Desktop',
            self::MOBILE => 'Mobile',
        };
    }
}
