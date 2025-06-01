<?php

declare(strict_types=1);

namespace App\Request\Token\Refresh;

use App\Request\Token\RefreshTokenRequestInterface;

readonly final class RefreshTokenRequest implements RefreshTokenRequestInterface
{
    public function __construct(
        private string $refresh_token
    ) {}

    public function getRefreshToken(): string
    {
        return $this->refresh_token;
    }
}