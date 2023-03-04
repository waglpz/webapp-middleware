<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware\Tests;

use LogicException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Waglpz\Webapp\Middleware\ApiAuthenticatorMiddleware;
use Waglpz\Webapp\Middleware\FirewallMiddleware;
use Waglpz\Webapp\Middleware\MiddlewareStackFactory;
use Waglpz\Webapp\Middleware\UserRolesMiddleware;
use Waglpz\Webapp\Security\Authenticator;
use Waglpz\Webapp\Security\AuthStorageInMemory;
use Waglpz\Webapp\Security\Firewalled;
use Waglpz\Webapp\Security\UserRolesProvider;

final class MiddlewareStackFactoryTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @test
     */
    public function itCreatesAMiddlewareStack(): void
    {
        $authStorage   = new AuthStorageInMemory();
        $authenticator = $this->createMock(Authenticator::class);
        $authenticator->expects(self::once())->method('authenticate')->willReturn(true);
        $rolesProvider = $this->createMock(UserRolesProvider::class);
        $firewall      = $this->createMock(Firewalled::class);
        $request       = $this->createMock(ServerRequestInterface::class);
        $response      = $this->createMock(ResponseInterface::class);
        $stream        = $this->createMock(StreamInterface::class);
        $stream->expects(self::once())->method('getContents')->willReturn('OK');
        $response->expects(self::once())->method('getBody')->willReturn($stream);
        $middlewares     = [
            new ApiAuthenticatorMiddleware($authenticator, $authStorage),
            new UserRolesMiddleware($rolesProvider, $authStorage),
            new FirewallMiddleware($firewall, $authStorage),
        ];
        $sut             = new MiddlewareStackFactory($middlewares);
        $finalHandler    = static fn () => $response;
        $middlewareStack = $sut->create($finalHandler);
        $fakt            = $middlewareStack($request);
        self::assertSame('OK', $fakt->getBody()->getContents());
    }

    /**
     * @throws Exception
     *
     * @test
     */
    public function itThrowsExceptionIfNoMiddlewaresWasRegistered(): void
    {
        $request     = $this->createMock(ServerRequestInterface::class);
        $middlewares = [];
        $sut         = new MiddlewareStackFactory($middlewares);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Middleware Stack was not properly created. Please use Create Method.');
        $fakt = $sut($request);
    }
}
