<?php

namespace App\Repository;

use App\Entity\UserProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class UserProfileRepository extends ServiceEntityRepository implements UserProfileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, UserProfile::class);
    }

    public function getProfileById(int $profile_id)
    {
        return $this->findOneBy(['id' => $profile_id]);
    }

    public function getUserProfile(int $user_id): ?UserProfile
    {
        return $this->findOneBy(['user' => $user_id]);
    }

    public function findProfilesByUsername(string $username, int $limit = 5)
    {
        return $this->createQueryBuilder('p')
            ->where('p.username LIKE :query')
            ->setParameter('query', '%' . $username . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function createOrUpdateProfile(UserProfile $userProfile): UserProfile
    {
        $this->entityManager->persist($userProfile);
        $this->entityManager->flush();

        return $userProfile;
    }
}
