<?php

declare(strict_types=1);

namespace App\UserCase\Token\Refresh;

use App\Request\Token\RefreshTokenRequestInterface;
use App\Response\ResponseInterface;
use App\Response\Token\RefreshTokenResponse;
use App\Service\Token\RefreshTokenServiceInterface;
use App\Service\Token\TokenTtlProviderInterface;

readonly final class RefreshTokenUserCase
{
    public function __construct(
        private RefreshTokenServiceInterface $refreshTokenService,
        private TokenTtlProviderInterface $tokenTtlProvider
    ) {}

    public function refreshAccessToken(RefreshTokenRequestInterface $refreshTokenRequest): ResponseInterface
    {
        $accessToken = $this->refreshTokenService->refreshAccessToken($refreshTokenRequest->getRefreshToken());
        $accessTokenTtl = $this->tokenTtlProvider->getAccessTtl();
        $createdAt = new \DateTimeImmutable();

        return new RefreshTokenResponse($accessToken, $accessTokenTtl, $createdAt);
    }
}
