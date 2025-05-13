<?php

namespace App\Service\Auth;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AuthService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    )
    {}

    public function getRefreshTtl(bool $isRemember): int
    {
        return $isRemember
            ? $this->parameterBag->get('gesdinet_jwt_refresh_token.ttl')
            : $this->parameterBag->get('lexik_jwt_authentication.token_ttl');
    }

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
