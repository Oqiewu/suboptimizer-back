<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\User\CreateUser;

use App\DTO\User\AbstractCreateUserDTO;
use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Service\User\CreateUser\CreateBaseUserService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class CreateBaseUserServiceTest extends TestCase
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

        $dto = $this->createMock(AbstractCreateUserDTO::class);
        $dto->expects($this->once())->method('getEmail')->willReturn($email);
        $dto->expects($this->once())->method('getPassword')->willReturn($password);
        $dto->expects($this->once())->method('getFirstName')->willReturn($firstName);
        $dto->expects($this->once())->method('getLastName')->willReturn($lastName);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(null);

        $userRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(User::class));

        $hasherMock = $this->getMockBuilder(PasswordHasherInterface::class)
            ->onlyMethods(['hash', 'verify', 'needsRehash'])
            ->getMock();

        $hasherMock->method('verify')->willReturn(true);
        $hasherMock->method('needsRehash')->willReturn(false);

        $hasherMock->expects($this->once())
            ->method('hash')
            ->with($password)
            ->willReturn($hashedPassword);

        $passwordHasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);
        $passwordHasherFactory
            ->expects($this->once())
            ->method('getPasswordHasher')
            ->with(User::class)
            ->willReturn($hasherMock);

        $service = new CreateBaseUserService($userRepository, $passwordHasherFactory);

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

        $dto = $this->createMock(AbstractCreateUserDTO::class);
        $dto->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(new User(
                email: 'existing@example.com',
                firstName: 'Existing',
                lastName: 'User',
                password: 'hashedPassword123'
            ));

        $passwordHasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);

        $service = new CreateBaseUserService($userRepository, $passwordHasherFactory);

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('User already exists.');

        $service->createUser($dto);
    }

}
