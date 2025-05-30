<?php

declare(strict_types=1);

namespace App\Response;

use App\Interface\Response\ResponseInterface;

final class RefreshTokenResponse implements ResponseInterface
{
    public function __construct(
        private readonly string $accessToken,
        private readonly int $accessTokenTtl,
        private readonly \DateTimeImmutable $createdAt,
    ) {}

    public function toArray(): array
    {
        $createdAt = $this->createdAt->format('Y-m-d H:i:s');

        return [
            'accessToken' => [
                'token' => $this->accessToken,
                'created_at' => $createdAt,
                'ttl' => $this->accessTokenTtl,
            ],
        ];
    }
}
