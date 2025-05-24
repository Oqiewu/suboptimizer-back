<?php

declare(strict_types=1);

namespace App\Service\Auth;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly final class AuthService
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    )
    {}

    public function collectResponseArray(string $accessToken, string $refreshToken, int $refreshTtl): array
    {
        return [
            'accessToken' => [
                'token' => $accessToken,
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'ttl' => $this->parameterBag->get('lexik_jwt_authentication.token_ttl'),
            ],
            'refreshToken' => [
                'token' => $refreshToken,
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'ttl' => $refreshTtl,
            ],
        ];
    }
}
