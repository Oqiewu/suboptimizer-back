<?php

declare(strict_types=1);

namespace App\Interface\Service\Token;

use App\Entity\User;

interface RefreshTokenServiceInterface
{
    public function createRefreshToken(User $user, int $refreshTtl): string;
    public function refreshAccessToken(string $refreshToken): string;
}