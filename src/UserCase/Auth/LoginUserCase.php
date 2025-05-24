<?php

declare(strict_types=1);

namespace App\UserCase\Auth;

use App\DTO\Auth\LoginRequestDTO;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\Token\RefreshTokenService;
use DateMalformedStringException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly final class LoginUserCase
{
    public function __construct(
        private AuthService $authService,
        private RefreshTokenService $refreshTokenService,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $JWTTokenManager,
    ){}

    /**
     * @param LoginRequestDTO $loginRequestDTO
     * @return array
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function authenticate(LoginRequestDTO $loginRequestDTO): array
    {
        $user = $this->userRepository->findOneBy(['email' => $loginRequestDTO->email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginRequestDTO->password)) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid email or password.');
        }

        $refreshTtl = $this->authService->getRefreshTtl($loginRequestDTO->is_remember);
        $accessToken = $this->JWTTokenManager->create($user);

        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

        return $this->authService->collectResponseArray($accessToken, $refreshToken, $refreshTtl);
    }
}