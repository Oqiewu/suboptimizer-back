<?php

declare(strict_types=1);

namespace App\Tests\Unit\UserCase\Auth;

use App\Interface\DTO\User\CreateUserDTOInterface;
use App\Interface\Request\RegisterRequestInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\Interface\Service\User\CreateUserServiceInterface;
use App\Interface\Response\ResponseInterface;
use App\UserCase\Auth\RegisterUserCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Throwable;
use Random\RandomException;
use PHPUnit\Framework\MockObject\Exception;
use DateMalformedStringException;
use Symfony\Component\Security\Core\User\UserInterface;

final class RegisterUserCaseTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws Exception
     * @throws RandomException
     * @throws Throwable
     */
    public function testRegister(): void
    {
        $dto = $this->createMock(CreateUserDTOInterface::class);
        $user = $this->createMock(UserInterface::class);
        $accessToken = 'access_token';
        $refreshToken = 'refresh_token';
        $accessTtl = 43200;
        $refreshTtl = 2592000;

        $registerRequest = $this->createMock(RegisterRequestInterface::class);
        $registerRequest
            ->expects($this->once())
            ->method('toDTO')
            ->willReturn($dto);

        $registerRequest
            ->expects($this->once())
            ->method('isRemember')
            ->willReturn(true);

        $createUserService = $this->createMock(CreateUserServiceInterface::class);
        $createUserService
            ->expects($this->once())
            ->method('createUser')
            ->with($dto)
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

        $userCase = new RegisterUserCase(
            $createUserService,
            $jwtManager,
            $refreshTokenService,
            $tokenTtlProvider
        );

        $result = $userCase->register($registerRequest);

        $this->assertInstanceOf(ResponseInterface::class, $result);

        $expected = [
            'accessToken' => [
                'token' => $accessToken,
                'created_at' => $result->toArray()['accessToken']['created_at'],
                'ttl' => $accessTtl,
            ],
            'refreshToken' => [
                'token' => $refreshToken,
                'created_at' => $result->toArray()['refreshToken']['created_at'],
                'ttl' => $refreshTtl,
            ],
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $result->toArray()['accessToken']['created_at']
        );
        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $result->toArray()['refreshToken']['created_at']
        );
    }
}
