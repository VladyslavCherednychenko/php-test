<?php
namespace App\Service;

use App\Dto\AuthDto;
use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function getUser(AuthDto $dto): ?User
    {
        $user = $this->userRepository->getUserByEmail($dto->email);

        if ($user === null) {
            return null;
        }

        if (! $this->passwordHasher->isPasswordValid($user, $dto->password)) {
            return null;
        }

        return $user;
    }
}
