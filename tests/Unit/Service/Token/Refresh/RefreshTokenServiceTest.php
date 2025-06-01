<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token\Refresh;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepositoryInterface;
use App\Service\Token\Refresh\RefreshTokenService;
use DateMalformedStringException;
use DateTime;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Random\RandomException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

final class RefreshTokenServiceTest extends TestCase
{
    private JWTTokenManagerInterface&MockObject $jwtManager;
    private RefreshTokenRepositoryInterface&MockObject $refreshTokenRepository;
    private RefreshTokenService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepositoryInterface::class);

        $this->service = new RefreshTokenService(
            $this->jwtManager,
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
        $refreshTtl = 43200;

        $this->refreshTokenRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn(null);

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
            ->method('findOneBy')
            ->with(['token' => $refreshTokenStr])
            ->willReturn($refreshToken);

        $this->jwtManager
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn('newAccessToken');

        $token = $this->service->refreshAccessToken($refreshTokenStr);
        $this->assertSame('newAccessToken', $token);
    }

    #[DataProvider('refreshAccessTokenFailsProvider')]
    /**
     * @throws Exception
     */
    public function testRefreshAccessTokenFails(
        string $token,
        bool $hasRefreshToken,
        bool $isExpired,
        string $expectedException,
        string $expectedMessage
    ): void {
        if ($hasRefreshToken) {
            $refreshTokenMock = $this->createMock(RefreshToken::class);
            $validAt = $isExpired ? new \DateTime('-1 hour') : new \DateTime('+1 hour');
            $refreshTokenMock->method('getValidAt')->willReturn($validAt);
        } else {
            $refreshTokenMock = null;
        }

        $this->refreshTokenRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => $token])
            ->willReturn($refreshTokenMock);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $this->service->refreshAccessToken($token);
    }

    public static function refreshAccessTokenFailsProvider(): array
    {
        return [
            'token not found' => [
                'token' => 'invalidtoken',
                'hasRefreshToken' => false,
                'isExpired' => false,
                'expectedException' => BadCredentialsException::class,
                'expectedMessage' => 'Invalid or expired refresh token.',
            ],
            'token expired' => [
                'token' => 'expiredtoken',
                'hasRefreshToken' => true,
                'isExpired' => true,
                'expectedException' => BadCredentialsException::class,
                'expectedMessage' => 'Invalid or expired refresh token.',
            ],
        ];
    }
}
