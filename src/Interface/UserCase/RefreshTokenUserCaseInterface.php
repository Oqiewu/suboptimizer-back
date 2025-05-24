<?php

declare(strict_types=1);

namespace App\Interface\UserCase;

use App\Interface\Request\RefreshTokenRequestInterface;

interface RefreshTokenUserCaseInterface
{
    public function refreshAccessToken(RefreshTokenRequestInterface $refreshTokenRequest): array;
}