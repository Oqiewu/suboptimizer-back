<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Interface\DTO\User\CreateUserDTOInterface;
use App\Interface\Service\User\CreateUserServiceInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly final class CreateUserService implements CreateUserServiceInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @throws ConflictHttpException
     */
    public function createUser(CreateUserDTOInterface $createUserDTO): User
    {
        if ($this->userRepository->findOneBy(['email' => $createUserDTO->getEmail()])) {
            throw new ConflictHttpException('User already exists.');
        }

        $user = new User();
        $user
            ->setEmail($createUserDTO->getEmail())
            ->setPassword($this->passwordHasher->hashPassword($user, $createUserDTO->getPassword()))
            ->setFirstName($createUserDTO->getFirstName())
            ->setLastName($createUserDTO->getLastName())
            ->setRoles(['ROLE_USER']);

        return $this->userRepository->createUser($user);
    }
}
