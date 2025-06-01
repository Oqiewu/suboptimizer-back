<?php

declare(strict_types=1);

namespace App\UserCase\Auth\Register;

use App\Request\Auth\RegisterRequestInterface;
use App\Response\ResponseInterface;
use App\Response\Token\AccessTokenResponse;
use App\Service\Token\RefreshTokenServiceInterface;
use App\Service\Token\TokenTtlProviderInterface;
use App\Service\User\CreateUserServiceInterface;
use DateMalformedStringException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Throwable;

readonly final class RegisterUserCase
{
    public function __construct(
        private CreateUserServiceInterface $createUserService,
        private JWTTokenManagerInterface $JWTTokenManager,
        private RefreshTokenServiceInterface $refreshTokenService,
        private TokenTtlProviderInterface $tokenTtlProvider,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Throwable
     */
    public function register(RegisterRequestInterface $registerRequest): ResponseInterface
    {
        $createUserDTO = $registerRequest->toDTO();
        $user = $this->createUserService->createUser($createUserDTO);

        $accessToken = $this->JWTTokenManager->create($user);
        $accessTtl = $this->tokenTtlProvider->getAccessTtl();

        $refreshTtl = $this->tokenTtlProvider->getRefreshTtl($registerRequest->isRemember());
        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

        return new AccessTokenResponse(
            accessToken: $accessToken,
            accessTokenTtl: $accessTtl,
            refreshToken: $refreshToken,
            refreshTokenTtl: $refreshTtl,
            issuedAt: new \DateTimeImmutable(),
        );
    }

}
