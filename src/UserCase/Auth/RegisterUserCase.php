<?php

declare(strict_types=1);

namespace App\UserCase\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\Interface\Service\User\CreateUserServiceInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
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
    public function register(RegisterRequestDTO $registerRequestDTO): array
    {
        $user = $this->createUserService->createUser($registerRequestDTO);

        $refreshTokenTtl = $this->tokenTtlProvider->getRefreshTtl($registerRequestDTO->isRemember());
        $accessTokenTtl  = $this->tokenTtlProvider->getAccessTtl();
        $accessToken     = $this->JWTTokenManager->create($user);
        $refreshToken    = $this->refreshTokenService->createRefreshToken($user, $refreshTokenTtl);

        return [
            'accessToken' => $accessToken,
            'accessTokenTtl' => $accessTokenTtl,
            'refreshToken' => $refreshToken,
            'refreshTokenTtl' => $refreshTokenTtl,
        ];
    }
}
