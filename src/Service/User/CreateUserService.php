<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Interface\DTO\User\CreateUserDTOInterface;
use App\Interface\Service\User\CreateUserServiceInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly final class CreateUserService implements CreateUserServiceInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @throws ConflictHttpException
     */
    public function createUser(CreateUserDTOInterface $createUserDTO): UserInterface
    {
        $email = $createUserDTO->getEmail();

        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new ConflictHttpException('User already exists.');
        }

        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($user, $createUserDTO->getPassword()))
            ->setFirstName($createUserDTO->getFirstName())
            ->setLastName($createUserDTO->getLastName())
            ->setRoles(['ROLE_USER']);

        return $this->userRepository->createUser($user);
    }
}
