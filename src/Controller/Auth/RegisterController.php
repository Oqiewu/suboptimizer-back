<?php

namespace App\Controller\Auth;

use App\Controller\Auth\DTO\RegisterRequestDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RefreshTokenService;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $JWTTokenManager,
        private readonly RefreshTokenService $refreshTokenService,
    ) {}

    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] RegisterRequestDTO $dto,
    ): JsonResponse {
        if ($this->userRepository->findOneBy(['email' => $dto->email])) {
            return $this->json(['message' => 'User already exists.'], Response::HTTP_CONFLICT);
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

            $accessToken = $this->JWTTokenManager->create($user);
            $refreshTtl = $dto->is_remember
                ? $this->getParameter('gesdinet_jwt_refresh_token.ttl')
                : 0;

            $this->refreshTokenService->removeExistingRefreshToken($user);
            $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

            return $this->json([
                'code' => 201,
                'message' => 'User registered successfully.',
                'result' => [
                    'accessToken' => [
                        'token' => $accessToken,
                        'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                        'ttl' => $this->getParameter('lexik_jwt_authentication.token_ttl'),
                    ],
                    'refreshToken' => [
                        'token' => $refreshToken,
                        'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                        'ttl' => $refreshTtl,
                    ],
                ]
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            if ($this->entityManager->contains($user)) {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
            }

            return $this->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
