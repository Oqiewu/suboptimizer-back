<?php

declare(strict_types=1);

namespace App\Response;

use App\Interface\Response\ResponseInterface;

readonly final class TokenResponse implements ResponseInterface
{
    public function __construct(
        private string $accessToken,
        private int $accessTokenTtl,
        private string $refreshToken,
        private int $refreshTokenTtl,
        private \DateTimeImmutable $issuedAt,
    ) {}

    public function toArray(): array
    {
        $createdAt = $this->issuedAt->format('Y-m-d H:i:s');

        return [
            'accessToken' => [
                'token' => $this->accessToken,
                'created_at' => $createdAt,
                'ttl' => $this->accessTokenTtl,
            ],
            'refreshToken' => [
                'token' => $this->refreshToken,
                'created_at' => $createdAt,
                'ttl' => $this->refreshTokenTtl,
            ],
        ];
    }
}
