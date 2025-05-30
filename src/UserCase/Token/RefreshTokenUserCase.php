<?php

declare(strict_types=1);

namespace App\UserCase\Token;

use App\Interface\Request\RefreshTokenRequestInterface;
use App\Interface\Response\ResponseInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\Interface\UserCase\RefreshTokenUserCaseInterface;
use App\Response\RefreshTokenResponse;

readonly final class RefreshTokenUserCase implements RefreshTokenUserCaseInterface
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
