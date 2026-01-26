<?php

namespace App\Service;

use App\Constants\RefreshTokenConstants;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepositoryInterface;

class RefreshTokenService implements RefreshTokenServiceInterface
{
    public function __construct(
        private RefreshTokenRepositoryInterface $repository
    ) {}

    public function createToken(User $user): RefreshToken
    {
        $token = new RefreshToken();
        $token->setUser($user);

        $token->setToken(bin2hex(random_bytes(32)));

        $expiresAt = new \DateTimeImmutable(sprintf('+%d seconds', RefreshTokenConstants::TOKEN_TTL));
        $token->setExpiresAt($expiresAt);

        $this->repository->save($token);

        return $token;
    }

    public function findValidToken(string $token): ?RefreshToken
    {
        if ($token == null || $token == '') {
            return null;
        }
        return $this->repository->findValidToken($token);
    }

    public function rotateToken(RefreshToken $refreshToken): RefreshToken
    {
        $user = $refreshToken->getUser();
        $this->repository->deleteToken($refreshToken);
        return $this->createToken($user);
    }
}
