<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use App\Service\Token\RefreshTokenService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use PHPUnit\Framework\MockObject\Exception;
use DateMalformedStringException;
use Random\RandomException;

final class RefreshTokenServiceTest extends TestCase
{
    private JWTTokenManagerInterface&MockObject $jwtManager;
    private EntityManagerInterface&MockObject $entityManager;
    private RefreshTokenRepository&MockObject $refreshTokenRepository;
    private RefreshTokenService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepository::class);

        $this->service = new RefreshTokenService(
            $this->jwtManager,
            $this->entityManager,
            $this->refreshTokenRepository
        );
    }

    /**
     * @throws DateMalformedStringException
     * @throws Exception
     * @throws RandomException
     */
    public function testCreateRefreshToken(): void
    {
        $user = $this->createMock(UserInterface::class);
        $refreshTtl = 3600;

        $this->refreshTokenRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn(null);

        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->callback(fn($token) => $token instanceof RefreshToken));
        $this->entityManager->expects($this->once())->method('flush');

        $token = $this->service->createRefreshToken($user, $refreshTtl);

        $this->assertIsString($token);
        $this->assertSame(128, strlen($token));
    }

    /**
     * @throws Exception
     */
    public function testRefreshAccessTokenSuccess(): void
    {
        $user = $this->createMock(UserInterface::class);
        $refreshTokenStr = 'validtoken';
        $validAt = new DateTime('+1 hour');

        $refreshToken = $this->createMock(RefreshToken::class);
        $refreshToken->method('getValidAt')->willReturn($validAt);
        $refreshToken->method('getUser')->willReturn($user);

        $this->refreshTokenRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with($refreshTokenStr)
            ->willReturn($refreshToken);

        $this->jwtManager
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn('newAccessToken');

        $token = $this->service->refreshAccessToken($refreshTokenStr);
        $this->assertSame('newAccessToken', $token);
    }

    public function testRefreshAccessTokenFailsIfTokenNotFound(): void
    {
        $this->refreshTokenRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with('invalidtoken')
            ->willReturn(null);

        $this->expectException(BadCredentialsException::class);
        $this->expectExceptionMessage('Invalid or expired refresh token.');

        $this->service->refreshAccessToken('invalidtoken');
    }

    /**
     * @throws Exception
     */
    public function testRefreshAccessTokenFailsIfTokenExpired(): void
    {
        $refreshToken = $this->createMock(RefreshToken::class);
        $refreshToken->method('getValidAt')->willReturn(new DateTime('-1 hour'));

        $this->refreshTokenRepository
            ->method('findByToken')
            ->willReturn($refreshToken);

        $this->expectException(BadCredentialsException::class);
        $this->expectExceptionMessage('Invalid or expired refresh token.');

        $this->service->refreshAccessToken('expiredtoken');
    }
}
