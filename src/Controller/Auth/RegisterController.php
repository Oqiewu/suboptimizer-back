<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        return $this->json(['message' => 'Register endpoint']);
    }
}