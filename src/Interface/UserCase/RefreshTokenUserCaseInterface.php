<?php

declare(strict_types=1);

namespace App\Interface\UserCase;

use App\Interface\Request\RefreshTokenRequestInterface;
use App\Interface\Response\ResponseInterface;

interface RefreshTokenUserCaseInterface
{
    public function refreshAccessToken(RefreshTokenRequestInterface $refreshTokenRequest): ResponseInterface;
}