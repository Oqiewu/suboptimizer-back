<?php

declare(strict_types=1);

namespace App\Service\Token\TtlProvider;

use App\Service\Token\TokenTtlProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly final class TokenTtlProvider implements TokenTtlProviderInterface
{
    public function __construct(private ParameterBagInterface $params) {}

    public function getRefreshTtl(bool $isRemember): int
    {
        return $isRemember
            ? $this->params->get('jwt_refresh_token.ttl')
            : $this->params->get('lexik_jwt_authentication.token_ttl');
    }

    public function getAccessTtl(): int
    {
        return $this->params->get('lexik_jwt_authentication.token_ttl');
    }
}