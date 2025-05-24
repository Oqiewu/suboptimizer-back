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
        $user = $this->createMock(UserInterface::class);;
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

        $result = $loginUserCase->authenticate($loginRequest);

        $this->assertSame([
            'accessToken' => $accessToken,
            'accessTokenTtl' => $accessTtl,
            'refreshToken' => $refreshToken,
            'refreshTokenTtl' => $refreshTtl,
        ], $result);
    }
}
