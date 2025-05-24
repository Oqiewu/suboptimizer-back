<?php

declare(strict_types=1);

namespace App\Request\Token;

use App\Interface\Request\RefreshTokenRequestInterface;

readonly class RefreshTokenRequest implements RefreshTokenRequestInterface
{
    public function __construct(
        private string $refresh_token
    ) {}

    public function getRefreshToken(): string
    {
        return $this->refresh_token;
    }
}