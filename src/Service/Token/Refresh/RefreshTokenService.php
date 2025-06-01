<?php

declare(strict_types=1);

namespace App\Service\Token\Refresh;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepositoryInterface;
use App\Service\Token\RefreshTokenServiceInterface;
use DateMalformedStringException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

readonly final class RefreshTokenService implements RefreshTokenServiceInterface
{
    public function __construct(
        private JWTTokenManagerInterface $JWTTokenManager,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function createRefreshToken(UserInterface $user, int $refreshTtl): string
    {
        $this->removeExistingRefreshToken($user);

        $validAt = new \DateTime();
        $validAt->modify('+' . $refreshTtl . ' seconds');

        $token = bin2hex(random_bytes(64));
        $refreshToken = new RefreshToken(
            user: $user,
            token: $token,
            validAt: $validAt
        );

        $this->refreshTokenRepository->create($refreshToken);

        return $token;
    }

    /**
     * @throws BadCredentialsException
     */
    public function refreshAccessToken(string $refreshToken): string
    {
        $storedToken = $this->refreshTokenRepository->findOneBy(['token' => $refreshToken]);

        if (!$storedToken || $storedToken->getValidAt() < new \DateTime()) {
            throw new BadCredentialsException('Invalid or expired refresh token.');
        }

        $user = $storedToken->getUser();
        return $this->JWTTokenManager->create($user);
    }

    private function removeExistingRefreshToken(UserInterface $user): void
    {
        $token = $this->refreshTokenRepository->findOneBy(['user' => $user]);

        if ($token) {
            $this->refreshTokenRepository->delete($token);
        }
    }
}
