<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepositoryInterface;

class RefreshTokenService implements RefreshTokenServiceInterface
{
    public function __construct(
        private RefreshTokenRepositoryInterface $repository
    ) {}

    public function createToken(User $user, int $ttl = 2592000): RefreshToken
    {
        $token = new RefreshToken();
        $token->setUser($user);

        $token->setToken(bin2hex(random_bytes(32)));

        $token->setExpiresAt((new \DateTime())->modify(sprintf('+%d seconds', $ttl)));

        $this->repository->save($token);

        return $token;
    }
}
