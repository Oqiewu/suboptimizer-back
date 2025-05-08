<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $JWTTokenManager,
        private readonly RefreshTokenService $refreshTokenService
    ) {}

    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'code' => 401,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $data = json_decode($request->getContent(), true);
        $remember = $data['remember'] ?? false;

        $refreshTtl = $remember
            ? $this->getParameter('gesdinet_jwt_refresh_token.ttl')
            : $this->getParameter('lexik_jwt_authentication.token_ttl');

        $accessToken = $this->JWTTokenManager->create($user);

        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $refreshTtl);

        return $this->json([
            'code' => 200,
            'message' => 'success',
            'result' => [
                'accessToken' => [
                    'token' => $accessToken,
                    'createdAt' => time(),
                    'ttl' => $this->getParameter('lexik_jwt_authentication.token_ttl'),
                ],
                'refreshToken' => [
                    'token' => $refreshToken,
                    'createdAt' => time(),
                    'ttl' => $refreshTtl,
                ],
            ],
        ]);
    }
}
