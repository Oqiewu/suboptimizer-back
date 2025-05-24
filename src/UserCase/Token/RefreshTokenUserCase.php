<?php

declare(strict_types=1);

namespace App\UserCase\Token;

use App\Interface\Request\RefreshTokenRequestInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\Interface\UserCase\RefreshTokenUserCaseInterface;

readonly final class RefreshTokenUserCase implements RefreshTokenUserCaseInterface
{
    public function __construct(
        private RefreshTokenServiceInterface $refreshTokenService,
        private TokenTtlProviderInterface $tokenTtlProvider
    ){}

    public function refreshAccessToken(RefreshTokenRequestInterface $refreshTokenRequest): array
    {
        $accessToken = $this->refreshTokenService->refreshAccessToken($refreshTokenRequest->getRefreshToken());
        $accessTokenTtl = $this->tokenTtlProvider->getAccessTtl();

        return [
            'accessToken' => $accessToken,
            'accessTokenTtl' => $accessTokenTtl,
        ];
    }
}