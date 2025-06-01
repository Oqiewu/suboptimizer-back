<?php

declare(strict_types=1);

namespace App\Controller\Token;

use App\Request\Token\Refresh\RefreshTokenRequest;
use App\UserCase\Token\Refresh\RefreshTokenUserCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/token')]
final class RefreshTokenController extends AbstractController
{
    public function __construct(
        private readonly RefreshTokenUserCase $refreshTokenUserCase,
    ) {}

    #[Route('/refresh', name: 'auth_refresh', methods: ['POST'])]
    public function refresh(
        #[MapRequestPayload] RefreshTokenRequest $refreshTokenRequest,
    ): JsonResponse {
        try {
            $result = $this->refreshTokenUserCase->refreshAccessToken($refreshTokenRequest);

            return $this->json([
                'code' => 200,
                'message' => 'success',
                'result' => [
                    'accessToken' => [
                        'token' => $result['accessToken'],
                        'created_at' => time(),
                        'ttl' => $result['accessTokenTtl'],
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'code' => 401,
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }
    }
}
