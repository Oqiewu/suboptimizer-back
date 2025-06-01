<?php

declare(strict_types=1);

namespace App\Service\Token;

use Symfony\Component\Security\Core\User\UserInterface;

interface RefreshTokenServiceInterface
{
    public function createRefreshToken(UserInterface $user, int $refreshTtl): string;
    public function refreshAccessToken(string $refreshToken): string;
}