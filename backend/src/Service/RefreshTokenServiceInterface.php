<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;

interface RefreshTokenServiceInterface
{
    public function createToken(User $user, int $ttl = 2592000): RefreshToken;
}
