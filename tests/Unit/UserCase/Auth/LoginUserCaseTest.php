<?php

declare(strict_types=1);

namespace App\Tests\Unit\UserCase\Auth;

use App\Interface\Request\LoginRequestInterface;
use App\Interface\Service\Auth\AuthenticateUserServiceInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\UserCase\Auth\LoginUserCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Interface\Response\ResponseInterface;

final class LoginUserCaseTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testAuthenticate(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'securePassword';
        $user = $this->createMock(UserInterface::class);
        $accessToken = 'access_token';
        $refreshToken = 'refresh_token';
        $accessTtl = 3600;
        $refreshTtl = 2592000;

        $loginRequest = $this->createMock(LoginRequestInterface::class);
        $loginRequest
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $loginRequest
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn($password);

        $loginRequest
            ->expects($this->once())
            ->method('isRemember')
            ->willReturn(true);

        $authService = $this->createMock(AuthenticateUserServiceInterface::class);
        $authService
            ->expects($this->once())
            ->method('authenticateUser')
            ->with($email, $password)
            ->willReturn($user);

        $tokenTtlProvider = $this->createMock(TokenTtlProviderInterface::class);
        $tokenTtlProvider
            ->expects($this->once())
            ->method('getAccessTtl')
            ->willReturn($accessTtl);

        $tokenTtlProvider
            ->expects($this->once())
            ->method('getRefreshTtl')
            ->with(true)
            ->willReturn($refreshTtl);

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn($accessToken);

        $refreshTokenService = $this->createMock(RefreshTokenServiceInterface::class);
        $refreshTokenService
            ->expects($this->once())
            ->method('createRefreshToken')
            ->with($user, $refreshTtl)
            ->willReturn($refreshToken);

        $loginUserCase = new LoginUserCase(
            $authService,
            $refreshTokenService,
            $tokenTtlProvider,
            $jwtManager
        );

        // Act
        $result = $loginUserCase->authenticate($loginRequest);

        // Assert
        $this->assertInstanceOf(ResponseInterface::class, $result);

        $resultArray = $result->toArray();

        $expected = [
            'accessToken' => [
                'token' => $accessToken,
                'created_at' => $resultArray['accessToken']['created_at'],
                'ttl' => $accessTtl,
            ],
            'refreshToken' => [
                'token' => $refreshToken,
                'created_at' => $resultArray['refreshToken']['created_at'],
                'ttl' => $refreshTtl,
            ],
        ];

        $this->assertEquals($expected, $resultArray);

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $resultArray['accessToken']['created_at']
        );

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $resultArray['refreshToken']['created_at']
        );
    }
}
