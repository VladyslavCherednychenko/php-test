<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use App\Entity\User;

interface RefreshTokenRepositoryInterface
{
    public function save(RefreshToken $refreshToken): void;

    public function findValidToken(string $token): ?RefreshToken;

    public function clearExpired(): int;

    public function deleteToken(RefreshToken $refreshToken): void;

    public function deleteAllTokensFromUser(User $user): void;
}
