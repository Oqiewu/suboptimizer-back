<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Controller\Auth\DTO\RegisterRequestDTO;
use App\UserCase\Auth\RegisterUserCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegisterUserCase $registerUserCase,
    ) {}

    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] RegisterRequestDTO $dto,
    ): JsonResponse {
        try {
            $result = $this->registerUserCase->register($dto);
            return $this->json([
                'code' => 201,
                'message' => 'User registered successfully.',
                'result' => $result,
            ], 201);
        } catch (\Throwable $e) {
            return $this->json([
                'code' => $e->getCode() ?: 500,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
