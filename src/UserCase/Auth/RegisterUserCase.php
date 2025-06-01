<?php

declare(strict_types=1);

namespace App\UserCase\Auth;

use App\Interface\Request\RegisterRequestInterface;
use App\Interface\Response\ResponseInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\Interface\Service\User\CreateUserServiceInterface;
use App\Interface\UserCase\RegisterUserCaseInterface;
use App\Response\TokenResponse;
use DateMalformedStringException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Throwable;

readonly final class RegisterUserCase implements RegisterUserCaseInterface
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

        return new TokenResponse(
            accessToken: $accessToken,
            accessTokenTtl: $accessTtl,
            refreshToken: $refreshToken,
            refreshTokenTtl: $refreshTtl,
            issuedAt: new \DateTimeImmutable(),
        );
    }

}
