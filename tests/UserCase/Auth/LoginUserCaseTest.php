<?php

declare(strict_types=1);

namespace App\Tests\UserCase\Auth;

use App\Controller\Auth\DTO\LoginRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\RefreshTokenService;
use App\UserCase\Auth\LoginUserCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginUserCaseTest extends TestCase
{
    private $authService;
    private $refreshTokenService;
    private $userRepository;
    private $passwordHasher;
    private $jwtTokenManager;

    private LoginUserCase $loginUserCase;

    protected function setUp(): void
    {
        $this->authService = $this->createMock(AuthService::class);
        $this->refreshTokenService = $this->createMock(RefreshTokenService::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->jwtTokenManager = $this->createMock(JWTTokenManagerInterface::class);

        $this->loginUserCase = new LoginUserCase(
            $this->authService,
            $this->refreshTokenService,
            $this->userRepository,
            $this->passwordHasher,
            $this->jwtTokenManager
        );
    }

    public function testAuthenticateSuccess(): void
    {
        $user = $this->createMock(User::class);

        $dto = new LoginRequestDTO();
        $dto->email = 'test@example.com';
        $dto->password = 'pass123';
        $dto->is_remember = true;

        $this->userRepository
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn($user);

        $this->passwordHasher
            ->method('isPasswordValid')
            ->with($user, 'pass123')
            ->willReturn(true);

        $this->authService
            ->method('getRefreshTtl')
            ->with(true)
            ->willReturn(3600);

        $this->jwtTokenManager
            ->method('create')
            ->with($user)
            ->willReturn('jwt.token.here');

        $this->refreshTokenService
            ->expects($this->once())
            ->method('removeExistingRefreshToken')
            ->with($user);

        $this->refreshTokenService
            ->method('createRefreshToken')
            ->with($user, 3600)
            ->willReturn('refresh.token.here');

        $this->authService
            ->method('collectResponseArray')
            ->willReturn([
                'accessToken' => [
                    'token' => 'jwt.token.here',
                    'createdAt' => '2025-05-12 00:00:00',
                    'ttl' => 3600,
                ],
                'refreshToken' => [
                    'token' => 'refresh.token.here',
                    'createdAt' => '2025-05-12 00:00:00',
                    'ttl' => 3600,
                ],
            ]);

        $response = $this->loginUserCase->authenticate($dto);

        $this->assertArrayHasKey('accessToken', $response);
        $this->assertArrayHasKey('refreshToken', $response);
        $this->assertEquals('jwt.token.here', $response['accessToken']['token']);
    }

    public function testAuthenticateWithInvalidUser(): void
    {
        $dto = new LoginRequestDTO();
        $dto->email = 'notfound@example.com';
        $dto->password = 'wrong';
        $dto->is_remember = false;

        $this->userRepository
            ->method('findOneBy')
            ->with(['email' => 'notfound@example.com'])
            ->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid email or password.');

        $this->loginUserCase->authenticate($dto);
    }

    public function testAuthenticateWithInvalidPassword(): void
    {
        $user = $this->createMock(User::class);

        $dto = new LoginRequestDTO();
        $dto->email = 'test@example.com';
        $dto->password = 'wrongpass';
        $dto->is_remember = false;

        $this->userRepository
            ->method('findOneBy')
            ->willReturn($user);

        $this->passwordHasher
            ->method('isPasswordValid')
            ->with($user, 'wrongpass')
            ->willReturn(false);

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid email or password.');

        $this->loginUserCase->authenticate($dto);
    }
}
