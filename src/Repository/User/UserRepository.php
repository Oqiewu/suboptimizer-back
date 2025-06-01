<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function create(UserInterface $user): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($user);
        $entityManager->flush();
    }

    public function update(UserInterface $user): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->flush();
    }

    public function delete(UserInterface $user): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($user);
        $entityManager->flush();
    }
}