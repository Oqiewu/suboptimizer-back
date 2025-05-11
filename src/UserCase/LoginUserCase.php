<?php

namespace App\UserCase;

use App\Controller\Auth\DTO\LoginRequestDTO;
use App\Repository\UserRepository;
use App\Service\Auth\LoginService;
use App\Service\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use DateMalformedStringException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginUserCase
{
    public function __construct(
        private readonly LoginService $loginService,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $JWTTokenManager,
    ){}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function authenticate(LoginRequestDTO $loginRequestDTO): array
    {
        $user = $this->userRepository->findOneBy(['email' => $loginRequestDTO->email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginRequestDTO->password)) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid email or password.');
        }

        $refreshTtl = $this->loginService->getRefreshTtl($loginRequestDTO->is_remember);
        $accessToken = $this->JWTTokenManager->create($user);

        $this->refreshTokenService->removeExistingRefreshToken($user);
        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

        return $this->loginService->collectResponseArray($accessToken, $refreshToken, $refreshTtl);

    }
}