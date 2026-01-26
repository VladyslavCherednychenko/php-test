<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;

interface RefreshTokenServiceInterface
{
    public function createToken(User $user): RefreshToken;

    public function findValidToken(string $token): ?RefreshToken;

    public function rotateToken(RefreshToken $refreshToken): RefreshToken;
}
