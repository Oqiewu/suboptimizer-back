<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token\TtlProvider;

use App\Service\Token\TtlProvider\TokenTtlProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class TokenTtlProviderTest extends TestCase
{
    #[DataProvider('ttlProvider')]
    /**
     * @throws Exception
     */
    public function testGetTtl(
        string $method,
        bool $remember,
        string $expectedParamName,
        int $expectedTtl
    ): void {
        $params = $this->createMock(ParameterBagInterface::class);
        $params
            ->expects($this->once())
            ->method('get')
            ->with($expectedParamName)
            ->willReturn($expectedTtl);

        $provider = new TokenTtlProvider($params);

        if ($method === 'getAccessTtl') {
            $this->assertSame($expectedTtl, $provider->getAccessTtl());
        } else {
            $this->assertSame($expectedTtl, $provider->getRefreshTtl($remember));
        }
    }

    public static function ttlProvider(): array
    {
        return [
            'access ttl' => ['getAccessTtl', false, 'lexik_jwt_authentication.token_ttl', 43200],
            'refresh ttl with remember' => ['getRefreshTtl', true, 'jwt_refresh_token.ttl', 2592000],
            'refresh ttl without remember' => ['getRefreshTtl', false, 'lexik_jwt_authentication.token_ttl', 43200],
        ];
    }
}
