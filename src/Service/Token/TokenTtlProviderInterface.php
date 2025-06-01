<?php

declare(strict_types=1);

namespace App\Service\Token;

interface TokenTtlProviderInterface
{
    public function getRefreshTtl(bool $isRemember): int;
    public function getAccessTtl(): int;
}