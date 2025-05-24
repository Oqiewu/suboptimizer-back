<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class RefreshTokenServiceTest extends TestCase
{
    private JWTTokenManagerInterface $jwtTokenManager;
    private EntityManagerInterface $entityManager;
    private RefreshTokenRepository $refreshTokenRepository;
    private RefreshTokenService $service;

    protected function setUp(): void
    {
        $this->jwtTokenManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepository::class);

        $this->service = new RefreshTokenService(
            $this->jwtTokenManager,
            $this->entityManager,
            $this->refreshTokenRepository
        );
    }

    public function testCreateRefreshToken(): void
    {
        $user = new User();
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $token = $this->service->createRefreshToken($user, 3600);

        $this->assertIsString($token);
        $this->assertEquals(128, strlen($token)); // 64 байта * 2 hex
    }

    public function testRefreshAccessTokenSuccess(): void
    {
        $user = new User();
        $refreshToken = new RefreshToken($user, 'token', (new \DateTime())->modify('+1 hour'));

        $this->refreshTokenRepository
            ->method('findByToken')
            ->willReturn($refreshToken);

        $this->jwtTokenManager
            ->method('create')
            ->with($user)
            ->willReturn('access_token');

        $result = $this->service->refreshAccessToken('token');

        $this->assertEquals('access_token', $result);
    }

    public function testRefreshAccessTokenFailsIfExpired(): void
    {
        $user = new User();
        $expiredToken = new RefreshToken($user, 'token', (new \DateTime())->modify('-1 hour'));

        $this->refreshTokenRepository
            ->method('findByToken')
            ->willReturn($expiredToken);

        $this->expectException(BadCredentialsException::class);
        $this->service->refreshAccessToken('token');
    }

    public function testRefreshAccessTokenFailsIfNotFound(): void
    {
        $this->refreshTokenRepository
            ->method('findByToken')
            ->willReturn(null);

        $this->expectException(BadCredentialsException::class);
        $this->service->refreshAccessToken('token');
    }

    public function testRemoveExistingRefreshToken(): void
    {
        $user = new User();
        $token = new RefreshToken($user, 'token', new \DateTime());

        $this->refreshTokenRepository
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn($token);

        $this->entityManager->expects($this->once())->method('remove')->with($token);
        $this->entityManager->expects($this->once())->method('flush');

        $this->service->removeExistingRefreshToken($user);
    }

    public function testRemoveExistingRefreshTokenWhenNotFound(): void
    {
        $user = new User();

        $this->refreshTokenRepository
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn(null);

        $this->entityManager->expects($this->never())->method('remove');
        $this->entityManager->expects($this->never())->method('flush');

        $this->service->removeExistingRefreshToken($user);
    }
}
