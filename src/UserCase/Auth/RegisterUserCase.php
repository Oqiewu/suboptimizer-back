<?php

declare(strict_types=1);

namespace App\UserCase\Auth;

use App\Interface\Request\RegisterRequestInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\Interface\Service\User\CreateUserServiceInterface;
use App\Interface\UserCase\RegisterUserCaseInterface;
use App\Request\Auth\RegisterRequest;
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
    public function register(RegisterRequestInterface $registerRequest): array
    {
        $createUserDTO = $registerRequest->toDTO();

        $user = $this->createUserService->createUser($createUserDTO);

        $refreshTokenTtl = $this->tokenTtlProvider->getRefreshTtl($registerRequest->isRemember());
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
