<?php

declare(strict_types=1);

namespace App\Interface\Service\Auth;

use Symfony\Component\Security\Core\User\UserInterface;

interface AuthenticateUserServiceInterface
{
    public function authenticateUser(string $email, string $password): UserInterface;
}