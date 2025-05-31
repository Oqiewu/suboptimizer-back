<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\AuthenticateUserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use PHPUnit\Framework\MockObject\Exception;

final class AuthenticateUserServiceTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private AuthenticateUserService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->service = new AuthenticateUserService($this->userRepository, $this->passwordHasher);
    }

    /**
     * @throws Exception
     */
    public function testAuthenticateUserSuccess(): void
    {
        $email = 'user@example.com';
        $password = 'secret';
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(true);

        $result = $this->service->authenticateUser($email, $password);

        $this->assertSame($user, $result);
    }

    public function testAuthenticateUserFailsIfUserNotFound(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'wrong@example.com'])
            ->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid email or password.');

        $this->service->authenticateUser('wrong@example.com', 'irrelevant');
    }

    /**
     * @throws Exception
     */
    public function testAuthenticateUserFailsIfPasswordInvalid(): void
    {
        $email = 'user@example.com';
        $password = 'wrong-password';
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(false);

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid email or password.');

        $this->service->authenticateUser($email, $password);
    }
}
