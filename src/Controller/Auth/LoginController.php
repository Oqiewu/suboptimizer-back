<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Controller\Auth\DTO\LoginRequestDTO;
use App\UserCase\Auth\LoginUserCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
class LoginController extends AbstractController
{
    public function __construct(
        private readonly LoginUserCase $loginUserCase,
    ) {}

    /**
     * @throws \Exception
     */
    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] LoginRequestDTO $loginRequestDTO
    ): JsonResponse
    {
        try {
            $result = $this->loginUserCase->authenticate($loginRequestDTO);
            return $this->json([
                'code' => 200,
                'message' => 'success',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => $e->getCode() ?: 500,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}