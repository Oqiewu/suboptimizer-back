<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Interface\DTO\User\CreateUserDTOInterface;
use App\Repository\UserRepository;
use App\Service\User\CreateUserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use PHPUnit\Framework\MockObject\Exception;

final class CreateUserServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreateUserSuccess(): void
    {
        $email = 'new@example.com';
        $password = 'plainPassword';
        $hashedPassword = 'hashedPassword';
        $firstName = 'John';
        $lastName = 'Doe';

        $dto = $this->createMock(CreateUserDTOInterface::class);
        $dto->expects($this->once())->method('getEmail')->willReturn($email);
        $dto->expects($this->once())->method('getPassword')->willReturn($password);
        $dto->expects($this->once())->method('getFirstName')->willReturn($firstName);
        $dto->expects($this->once())->method('getLastName')->willReturn($lastName);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(null);

        $userRepository
            ->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (User $user) use ($email, $hashedPassword, $firstName, $lastName) {
                return
                    $user->getEmail() === $email &&
                    $user->getPassword() === $hashedPassword &&
                    $user->getFirstName() === $firstName &&
                    $user->getLastName() === $lastName &&
                    $user->getRoles() === ['ROLE_USER'];
            }))
            ->willReturnCallback(fn(User $user) => $user);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), $password)
            ->willReturn($hashedPassword);

        $service = new CreateUserService($userRepository, $passwordHasher);

        $result = $service->createUser($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($email, $result->getEmail());
        $this->assertSame($hashedPassword, $result->getPassword());
        $this->assertSame($firstName, $result->getFirstName());
        $this->assertSame($lastName, $result->getLastName());
        $this->assertSame(['ROLE_USER'], $result->getRoles());
    }

    /**
     * @throws Exception
     */
    public function testCreateUserThrowsConflictIfUserExists(): void
    {
        $email = 'existing@example.com';

        $dto = $this->createMock(CreateUserDTOInterface::class);
        $dto->expects($this->once())->method('getEmail')->willReturn($email);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(new User());

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $service = new CreateUserService($userRepository, $passwordHasher);

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('User already exists.');

        $service->createUser($dto);
    }
}
