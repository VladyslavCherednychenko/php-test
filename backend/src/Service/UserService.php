<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function getUserList(int $page = 1, int $limit = 10): Paginator
    {
        $limit  = min(100, $limit);
        $offset = ($page - 1) * $limit;

        return $this->userRepository->getUserList($offset, $limit);
    }

    public function getUserById(int $id): ?User
    {
        if ($id < 1) {
            return null;
        }
        return $this->userRepository->getUserById($id);
    }

    public function createUser(User $user): User
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        return $this->userRepository->createUser($user);
    }
}
