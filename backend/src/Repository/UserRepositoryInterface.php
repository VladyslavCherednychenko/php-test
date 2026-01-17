<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface UserRepositoryInterface
{
    public function getUserList(int $page, int $offset): Paginator;
    public function getUserById(int $id): ?User;
    public function createUser(User $user): User;
}
