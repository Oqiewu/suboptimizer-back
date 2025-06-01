<?php

declare(strict_types=1);

namespace App\Repository\Token;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
final class RefreshTokenRepository extends ServiceEntityRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function create(RefreshToken $refreshToken): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($refreshToken);
        $entityManager->flush();
    }

    public function update(RefreshToken $refreshToken): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->flush();
    }

    public function delete(RefreshToken $refreshToken): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($refreshToken);
        $entityManager->flush();
    }

    public function findByToken(string $token): ?RefreshToken
    {
        return $this->findOneBy(['token' => $token]);
    }
}