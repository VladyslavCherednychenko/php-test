<?php
namespace App\Repository;

use App\Entity\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function save(RefreshToken $refreshToken): void;

    public function findValidToken(string $token): ?RefreshToken;

    public function clearExpired(): int;

    public function deleteToken(RefreshToken $refreshToken): void;
}
