<?php

declare(strict_types=1);

namespace App\Response;

use App\Interface\Response\ResponseInterface;

final class TokenResponse implements ResponseInterface
{
    public function __construct(
        private readonly string $accessToken,
        private readonly int $accessTokenTtl,
        private readonly string $refreshToken,
        private readonly int $refreshTokenTtl,
        private readonly \DateTimeImmutable $issuedAt,
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
