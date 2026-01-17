<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface UserServiceInterface
{
    public function getUserList(int $page, int $limit): Paginator;
    public function getUserById(int $id): ?User;
    public function createUser(User $user): User;
}
