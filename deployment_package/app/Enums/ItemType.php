<?php

declare(strict_types=1);

namespace App\Enums;

enum ItemType: string
{
    case PRODUCT = 'product';
    case SERVICE = 'service';

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT => 'Product',
            self::SERVICE => 'Service',
        };
    }
}
