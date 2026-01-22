<?php
namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function save(RefreshToken $refreshToken): void
    {
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
    }

    public function findValidToken(string $token): ?RefreshToken
    {
        return $this->createQueryBuilder('r')
            ->where('r.token = :token')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function clearExpired(): int
    {
        return $this->entityManager
            ->createQuery('DELETE FROM App\Entity\RefreshToken r WHERE r.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->execute();
    }

    public function deleteToken(RefreshToken $refreshToken): void
    {
        $this->entityManager->remove($refreshToken);
    }
}
