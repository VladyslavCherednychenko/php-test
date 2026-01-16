<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserRepository extends ServiceEntityRepository implements IUserRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUserList(int $page = 1, int $limit = 10): Paginator
    {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }

    public function getUserById(int $id): ?User
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function createUser(string $email, string $hashedPassword): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($hashedPassword);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }
}
