<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use DateMalformedStringException;
use Random\RandomException;

class RefreshTokenService
{
    public function __construct(
        private readonly JWTTokenManagerInterface $JWTTokenManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly RefreshTokenRepository $refreshTokenRepository,
    ) {}

    /**
     * @param User $user
     * @param int $refreshTtl
     * @return string
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function createRefreshToken(User $user, int $refreshTtl): string
    {
        $validAt = new \DateTime();
        $validAt->modify('+' . $refreshTtl . ' seconds');

        $token = bin2hex(random_bytes(64));
        $refreshToken = new RefreshToken($user, $token, $validAt);

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $token;
    }

    /**
     * Обновление access token с использованием refresh token
     *
     * @param string $refreshToken
     * @return string
     * @throws BadCredentialsException
     */
    public function refreshAccessToken(string $refreshToken): string
    {
        $storedToken = $this->refreshTokenRepository->findByToken($refreshToken);

        if (!$storedToken || $storedToken->getValidAt() < new \DateTime()) {
            throw new BadCredentialsException('Invalid or expired refresh token.');
        }

        $user = $storedToken->getUser();
        return $this->JWTTokenManager->create($user);
    }

    public function removeExistingRefreshToken(User $user): void
    {
        $token = $this->refreshTokenRepository->findOneBy(['user' => $user]);

        if ($token) {
            $this->entityManager->remove($token);
            $this->entityManager->flush();
        }
    }
}
