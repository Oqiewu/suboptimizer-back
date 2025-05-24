<?php

declare(strict_types=1);

namespace App\UserCase\Auth;

use App\Controller\Auth\DTO\RegisterRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;
use Random\RandomException;
use DateMalformedStringException;

class RegisterUserCase
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $JWTTokenManager,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly AuthService $authService,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Throwable
     */
    public function register(RegisterRequestDTO $dto): array
    {
        if ($this->userRepository->findOneBy(['email' => $dto->email])) {
            throw new ConflictHttpException('User already exists.');
        }

        $user = new User();
        $user
            ->setEmail($dto->email)
            ->setPassword($this->passwordHasher->hashPassword($user, $dto->password))
            ->setFirstName($dto->first_name)
            ->setLastName($dto->last_name)
            ->setRoles(['ROLE_USER']);

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $refreshTtl = $this->authService->getRefreshTtl($dto->is_remember);
            $accessToken = $this->JWTTokenManager->create($user);

            $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

            return $this->authService->collectResponseArray($accessToken, $refreshToken, $refreshTtl);
        } catch (\Throwable $e) {
            if ($this->entityManager->contains($user)) {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
            }

            throw $e;
        }
    }
}
