<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Interface\Service\Auth\AuthenticateUserServiceInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

readonly final class AuthenticateUserService implements AuthenticateUserServiceInterface
{
    public function __construct(
        private UserRepository $userRepository,
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
