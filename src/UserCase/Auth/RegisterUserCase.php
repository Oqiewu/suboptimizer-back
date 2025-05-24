<?php

declare(strict_types=1);

namespace App\UserCase\Auth;

use App\DTO\Auth\RegisterRequestDTO;
use App\Interface\Service\User\CreateUserServiceInterface;
use App\Service\Auth\AuthService;
use App\Service\Token\RefreshTokenService;
use DateMalformedStringException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Throwable;

readonly final class RegisterUserCase
{
    public function __construct(
        private CreateUserServiceInterface $createUserService,
        private JWTTokenManagerInterface $JWTTokenManager,
        private RefreshTokenService $refreshTokenService,
        private AuthService $authService,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Throwable
     */
    public function register(RegisterRequestDTO $registerRequestDTO): array
    {
        $user = $this->createUserService->createUser($registerRequestDTO);

        $refreshTtl = $this->authService->getRefreshTtl($registerRequestDTO->isRemember());
        $accessToken = $this->JWTTokenManager->create($user);
        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

        return $this->authService->collectResponseArray($accessToken, $refreshToken, $refreshTtl);
    }
}
