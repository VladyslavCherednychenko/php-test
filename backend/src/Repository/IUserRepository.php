<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface IUserRepository
{
    public function getUserList(int $page, int $limit): Paginator;
    public function getUserById(int $id): ?User;
    public function createUser(string $email, string $hashedPassword): User;
}
