<?php

namespace App\Enum;

enum UserRole: int
{
    case USER = 0;
    case MODERATOR = 1;
    case ADMIN = 2;

    public function canModerate(): bool
    {
        return $this === self::MODERATOR || $this === self::ADMIN;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::USER => 'user',
            self::MODERATOR => 'moderator',
            self::ADMIN => 'admin',
        };
    }
}
