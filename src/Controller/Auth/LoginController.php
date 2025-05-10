<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RefreshTokenService;
use App\Controller\Auth\DTO\LoginRequestDTO;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use DateMalformedStringException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/auth')]
class LoginController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $JWTTokenManager,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] LoginRequestDTO $loginRequestDTO
    ): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $loginRequestDTO->email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginRequestDTO->password)) {
            return $this->json([
                'code' => 401,
                'message' => 'Invalid credentials',
            ], 401);
        }


        $is_remember = $loginRequestDTO->is_remember;

        $refreshTtl = $is_remember
            ? $this->getParameter('gesdinet_jwt_refresh_token.ttl')
            : $this->getParameter('lexik_jwt_authentication.token_ttl');

        $accessToken = $this->JWTTokenManager->create($user);

        $this->refreshTokenService->removeExistingRefreshToken($user);
        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

        return $this->json([
            'code' => 200,
            'message' => 'success',
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
            ],
        ]);
    }
}
