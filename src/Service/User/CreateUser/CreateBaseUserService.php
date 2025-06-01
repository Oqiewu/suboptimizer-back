<?php

declare(strict_types=1);

namespace App\Service\User\CreateUser;

use App\DTO\User\AbstractCreateUserDTO;
use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Service\User\CreateUserServiceInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly final class CreateBaseUserService implements CreateUserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {}

    /**
     * @throws ConflictHttpException
     */
    public function createUser(AbstractCreateUserDTO $createUserDTO): UserInterface
    {
        $email = $createUserDTO->getEmail();

        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new ConflictHttpException('User already exists.');
        }

        $hasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        $user = new User(
            email: $email,
            firstName: $createUserDTO->getFirstName(),
            lastName: $createUserDTO->getLastName(),
            password: $hasher->hash($createUserDTO->getPassword()),
        );

        $this->userRepository->create($user);

        return $user;
    }
}

