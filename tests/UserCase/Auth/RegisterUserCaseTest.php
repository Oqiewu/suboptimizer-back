<?php

declare(strict_types=1);

namespace App\Tests\UserCase\Auth;

use App\Controller\Auth\DTO\RegisterRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\RefreshTokenService;
use App\UserCase\Auth\RegisterUserCase;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use PHPUnit\Framework\MockObject\Exception;
use DateMalformedStringException;
use \Random\RandomException;
use Throwable;

class RegisterUserCaseTest extends TestCase
{
    private $entityManager;
    private $userRepository;
    private $passwordHasher;
    private $jwtManager;
    private $refreshTokenService;
    private $authService;
    private $userCase;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->refreshTokenService = $this->createMock(RefreshTokenService::class);
        $this->authService = $this->createMock(AuthService::class);

        $this->userCase = new RegisterUserCase(
            $this->entityManager,
            $this->userRepository,
            $this->passwordHasher,
            $this->jwtManager,
            $this->refreshTokenService,
            $this->authService
        );
    }

    /**
     * @throws Throwable
     */
    public function testRegisterSuccess(): void
    {
        $dto = new RegisterRequestDTO();
        $dto->email = 'test@example.com';
        $dto->password = 'securepass';
        $dto->first_name = 'John';
        $dto->last_name = 'Doe';
        $dto->is_remember = true;

        $this->userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashedpass');

        $this->entityManager
            ->expects($this->once())
            ->method('persist');
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->jwtManager
            ->expects($this->once())
            ->method('create')
            ->willReturn('access_token');

        $this->authService
            ->expects($this->once())
            ->method('getRefreshTtl')
            ->with(true)
            ->willReturn(3600);

        $this->refreshTokenService
            ->expects($this->once())
            ->method('createRefreshToken')
            ->willReturn('refresh_token');

        $this->authService
            ->expects($this->once())
            ->method('collectResponseArray')
            ->with('access_token', 'refresh_token', 3600)
            ->willReturn([
                'token' => 'access_token',
                'refresh_token' => 'refresh_token',
                'expires_in' => 3600
            ]);

        $result = $this->userCase->register($dto);

        $this->assertEquals('access_token', $result['token']);
        $this->assertEquals('refresh_token', $result['refresh_token']);
        $this->assertEquals(3600, $result['expires_in']);
    }

    /**
     * @throws Throwable
     */
    public function testRegisterUserAlreadyExists(): void
    {
        $dto = new RegisterRequestDTO();
        $dto->email = 'existing@example.com';
        $dto->password = 'securepass';
        $dto->first_name = 'Jane';
        $dto->last_name = 'Smith';
        $dto->is_remember = false;

        $this->userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'existing@example.com'])
            ->willReturn(new User());

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('User already exists.');

        $this->userCase->register($dto);
    }

    /**
     * @throws Throwable
     */
    public function testRegisterWithExceptionDuringFlush(): void
    {
        $dto = new RegisterRequestDTO();
        $dto->email = 'fail@example.com';
        $dto->password = 'securepass';
        $dto->first_name = 'Fail';
        $dto->last_name = 'Case';
        $dto->is_remember = false;

        $this->userRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->passwordHasher
            ->method('hashPassword')
            ->willReturn('hashedpass');

        $this->entityManager
            ->method('persist')
            ->with($this->isInstanceOf(User::class));

        $this->entityManager
            ->method('flush')
            ->willThrowException(new \Exception('DB error'));

        $this->entityManager
            ->method('contains')
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('remove');
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DB error');

        $this->userCase->register($dto);
    }
}
