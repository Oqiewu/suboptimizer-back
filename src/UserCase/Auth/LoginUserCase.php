<?php

declare(strict_types=1);

namespace App\UserCase\Auth;

use App\Interface\Request\LoginRequestInterface;
use App\Interface\Response\ResponseInterface;
use App\Interface\Service\Auth\AuthenticateUserServiceInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\Interface\UserCase\LoginUserCaseInterface;
use App\Response\TokenResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

readonly final class LoginUserCase implements LoginUserCaseInterface
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

        return new TokenResponse(
            accessToken: $accessToken,
            accessTokenTtl: $accessTokenTtl,
            refreshToken: $refreshToken,
            refreshTokenTtl: $refreshTokenTtl,
            issuedAt: new \DateTimeImmutable(),
        );
    }
}
