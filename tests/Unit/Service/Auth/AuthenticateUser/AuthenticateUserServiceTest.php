<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth\AuthenticateUser;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Service\Auth\AuthenticateUser\AuthenticateUserService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class AuthenticateUserServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private AuthenticateUserService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
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

    #[DataProvider('invalidAuthenticateProvider')]
    /**
     * @throws Exception
     */
    public function testAuthenticateUserFails(
        string $email,
        bool $userExists,
        string $password,
        bool $isPasswordValid
    ): void {
        $user = $userExists ? $this->createMock(User::class) : null;

        $this->userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        if ($user !== null) {
            $this->passwordHasher
                ->expects($this->once())
                ->method('isPasswordValid')
                ->with($user, $password)
                ->willReturn($isPasswordValid);
        } else {
            $this->passwordHasher
                ->expects($this->never())
                ->method('isPasswordValid');
        }

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid email or password.');

        $this->service->authenticateUser($email, $password);
    }

    public static function invalidAuthenticateProvider(): array
    {
        return [
            'User not found' => ['wrong@example.com', false, 'irrelevant', false],
            'Invalid password' => ['user@example.com', true, 'wrong-password', false],
        ];
    }
}
