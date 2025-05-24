<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Interface\UserCase\RegisterUserCaseInterface;
use App\Request\Auth\RegisterRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegisterUserCaseInterface $registerUserCase,
    ) {}

    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] RegisterRequest $registerRequest,
    ): JsonResponse {
        try {
            $result = $this->registerUserCase->register($registerRequest);
            $now = new \DateTimeImmutable();

            return $this->json([
                'code' => 201,
                'message' => 'User registered successfully.',
                'result' => [
                    'accessToken' => [
                        'token' => $result['accessToken'],
                        'created_at' => $now->format('Y-m-d H:i:s'),
                        'ttl' => $result['accessTokenTtl']
                    ],
                    'refreshToken' => [
                        'token' => $result['refreshToken'],
                        'created_at' => $now->format('Y-m-d H:i:s'),
                        'ttl' => $result['refreshTokenTtl'],
                    ],
                ],
            ], 201);
        } catch (\Throwable $e) {
            return $this->json([
                'code' => $e->getCode() ?: 500,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
