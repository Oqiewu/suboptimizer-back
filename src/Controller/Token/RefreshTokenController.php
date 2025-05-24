<?php

declare(strict_types=1);

namespace App\Controller\Token;

use App\Service\Token\RefreshTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/token')]
final class RefreshTokenController extends AbstractController
{
    public function __construct(
        private readonly RefreshTokenService $refreshTokenService
    ) {}

    #[Route('/refresh', name: 'auth_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $refreshToken = $data['refreshToken'] ?? null;

        if (!$refreshToken) {
            return $this->json([
                'code' => 400,
                'message' => 'No refresh token provided',
            ], 400);
        }

        try {
            $accessToken = $this->refreshTokenService->refreshAccessToken($refreshToken);

            return $this->json([
                'code' => 200,
                'message' => 'success',
                'result' => [
                    'accessToken' => [
                        'token' => $accessToken,
                        'createdAt' => time(),
                        'ttl' => 3600,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 401,
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }
    }
}