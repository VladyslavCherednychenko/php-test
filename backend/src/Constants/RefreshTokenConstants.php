<?php

namespace App\Constants;

final readonly class RefreshTokenConstants
{
    public const COOKIE_NAME = 'REFRESH_TOKEN';

    public const COOKIE_PATH = '/api/auth/token';

    public const REMEMBER_ME_TTL = 2592000; // 60 * 60 * 24 * 30

    public const SESSION_TTL = 10800; // 60 * 60 * 3
}
