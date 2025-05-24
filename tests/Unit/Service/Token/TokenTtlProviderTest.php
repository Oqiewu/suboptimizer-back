<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Service\Token\TokenTtlProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PHPUnit\Framework\MockObject\Exception;

final class TokenTtlProviderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetAccessTtl(): void
    {
        $expectedTtl = 43200;

        $params = $this->createMock(ParameterBagInterface::class);
        $params
            ->expects($this->once())
            ->method('get')
            ->with('lexik_jwt_authentication.token_ttl')
            ->willReturn($expectedTtl);

        $provider = new TokenTtlProvider($params);

        $this->assertSame($expectedTtl, $provider->getAccessTtl());
    }

    /**
     * @throws Exception
     */
    public function testGetRefreshTtlWithRemember(): void
    {
        $expectedTtl = 2592000;

        $params = $this->createMock(ParameterBagInterface::class);
        $params
            ->expects($this->once())
            ->method('get')
            ->with('gesdinet_jwt_refresh_token.ttl')
            ->willReturn($expectedTtl);

        $provider = new TokenTtlProvider($params);

        $this->assertSame($expectedTtl, $provider->getRefreshTtl(true));
    }

    /**
     * @throws Exception
     */
    public function testGetRefreshTtlWithoutRemember(): void
    {
        $expectedTtl = 43200;

        $params = $this->createMock(ParameterBagInterface::class);
        $params
            ->expects($this->once())
            ->method('get')
            ->with('lexik_jwt_authentication.token_ttl')
            ->willReturn($expectedTtl);

        $provider = new TokenTtlProvider($params);

        $this->assertSame($expectedTtl, $provider->getRefreshTtl(false));
    }
}
