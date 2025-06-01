<?php

declare(strict_types=1);

namespace App\UserCase\Auth\Login;

use App\Request\Auth\LoginRequestInterface;
use App\Response\ResponseInterface;
use App\Response\Token\AccessTokenResponse;
use App\Service\Auth\AuthenticateUserServiceInterface;
use App\Service\Token\RefreshTokenServiceInterface;
use App\Service\Token\TokenTtlProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

readonly final class LoginUserCase
{
    public function __construct(
        private AuthenticateUserServiceInterface $authenticateUserService,
        private RefreshTokenServiceInterface $refreshTokenService,
        private TokenTtlProviderInterface $tokenTtlProvider,
        private JWTTokenManagerInterface $JWTTokenManager,
    ) {}

    public function authenticate(LoginRequestInterface $loginRequest): ResponseInterface
    {
        $user = $this->authenticateUserService->authenticateUser($loginRequest->getEmail(), $loginRequest->getPassword());

        $refreshTokenTtl = $this->tokenTtlProvider->getRefreshTtl($loginRequest->isRemember());
        $accessTokenTtl  = $this->tokenTtlProvider->getAccessTtl();

        $accessToken = $this->JWTTokenManager->create($user);
        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTokenTtl);

        return new AccessTokenResponse(
            accessToken: $accessToken,
            accessTokenTtl: $accessTokenTtl,
            refreshToken: $refreshToken,
            refreshTokenTtl: $refreshTokenTtl,
            issuedAt: new \DateTimeImmutable(),
        );
    }
}
