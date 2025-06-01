<?php

declare(strict_types=1);

namespace App\Tests\Unit\UserCase\Token;

use App\Interface\Request\RefreshTokenRequestInterface;
use App\Interface\Response\ResponseInterface;
use App\Interface\Service\Token\RefreshTokenServiceInterface;
use App\Interface\Service\Token\TokenTtlProviderInterface;
use App\UserCase\Token\RefreshTokenUserCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;

final class RefreshTokenUserCaseTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testRefreshAccessToken(): void
    {
        $refreshToken = 'sample_refresh_token';
        $newAccessToken = 'new_access_token';
        $ttl = 43200;

        $refreshTokenRequest = $this->createMock(RefreshTokenRequestInterface::class);
        $refreshTokenRequest
            ->expects($this->once())
            ->method('getRefreshToken')
            ->willReturn($refreshToken);

        $refreshTokenService = $this->createMock(RefreshTokenServiceInterface::class);
        $refreshTokenService
            ->expects($this->once())
            ->method('refreshAccessToken')
            ->with($refreshToken)
            ->willReturn($newAccessToken);

        $tokenTtlProvider = $this->createMock(TokenTtlProviderInterface::class);
        $tokenTtlProvider
            ->expects($this->once())
            ->method('getAccessTtl')
            ->willReturn($ttl);

        $userCase = new RefreshTokenUserCase($refreshTokenService, $tokenTtlProvider);

        $result = $userCase->refreshAccessToken($refreshTokenRequest);

        $this->assertInstanceOf(ResponseInterface::class, $result);

        $resultArray = $result->toArray();

        $expected = [
            'accessToken' => [
                'token' => $newAccessToken,
                'created_at' => $resultArray['accessToken']['created_at'],
                'ttl' => $ttl,
            ],
        ];

        $this->assertEquals($expected, $resultArray);

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $result->toArray()['accessToken']['created_at']
        );
    }
}
