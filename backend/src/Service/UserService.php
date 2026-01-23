<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage
    ) {}

    public function getUserList(int $page = 1, int $limit = 10): Paginator
    {
        $limit = min(100, $limit);
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

    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->getUserByEmail($email);
    }

    public function createUser(User $user): User
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        return $this->userRepository->createUser($user);
    }

    public function getCurrentUser()
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            return $token->getUser();
        }
        return null;
    }
}
