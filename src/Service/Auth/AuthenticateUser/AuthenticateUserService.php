<?php

declare(strict_types=1);

namespace App\Service\Auth\AuthenticateUser;

use App\Repository\UserRepositoryInterface;
use App\Service\Auth\AuthenticateUserServiceInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly final class AuthenticateUserService implements AuthenticateUserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function authenticateUser(string $email, string $password): UserInterface
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid email or password.');
        }

        return $user;
    }
}
