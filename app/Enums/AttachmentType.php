<?php

namespace App\Enums;

enum AttachmentType: string
{
    case IMAGE = 'image';
    case FILE = 'file';

    public static function values(): array
    {
        return array_map(fn(self $type) => $type->value, self::cases());
    }
}
