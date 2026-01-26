<?php

namespace App\Constants;

final readonly class RefreshTokenConstants
{
    public const COOKIE_NAME = 'REFRESH_TOKEN';

    public const COOKIE_PATH = '/api/auth/token/refresh';

    public const TOKEN_TTL = 2592000;
}
