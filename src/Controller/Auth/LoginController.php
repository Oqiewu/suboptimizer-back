<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Interface\UserCase\LoginUserCaseInterface;
use App\Request\Auth\LoginRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
final class LoginController extends AbstractController
{
    public function __construct(
        private readonly LoginUserCaseInterface $loginUserCase,
    ) {}

    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] LoginRequest $loginRequestDTO
    ): JsonResponse {
        try {
            $result = $this->loginUserCase->authenticate($loginRequestDTO);

            return $this->json([
                'code' => 200,
                'message' => 'User logged in successfully.',
                'result' => $result->toArray(),
            ], 200);
        } catch (\Throwable $e) {
            return $this->json([
                'code' => $e->getCode() ?: 500,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
