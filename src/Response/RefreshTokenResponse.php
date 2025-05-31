<?php

declare(strict_types=1);

namespace App\Response;

use App\Interface\Response\ResponseInterface;

readonly final class RefreshTokenResponse implements ResponseInterface
{
    public function __construct(
        private string $accessToken,
        private int $accessTokenTtl,
        private \DateTimeImmutable $createdAt,
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
