<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Middleware\FirewallMiddleware;
use Waglpz\Webapp\Security\AuthStorage;
use Waglpz\Webapp\Security\Firewall;

final class FirewallMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new AuthStorage())->reset();
    }

    /** @test */
    public function itHasACorrectBehaviour(): void
    {
        $authStorage        = new AuthStorage();
        $authStorage->roles = ['ROLE_ABC'];
        $regeln             = [
            '/abc' => ['ROLE_ABC'],
        ];
        $firewall           = new Firewall($regeln);
        $firewallMiddleware = new FirewallMiddleware($firewall, $authStorage);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getRequestTarget')->willReturn('/abc');
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;
        $firewallMiddleware($request, $next);
    }

    /** @test */
    public function itThrownForbiddenExceptionWithStatusCode403(): void
    {
        $authStorage        = new AuthStorage();
        $authStorage->roles = ['ROLE_ABC'];
        $regeln             = [
            '/abc' => ['ROLE_ABC'],
        ];
        $firewall           = new Firewall($regeln);
        $firewallMiddleware = new FirewallMiddleware($firewall, $authStorage);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getRequestTarget')->willReturn('/def');
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;

        $this->expectException(\Phpro\ApiProblem\Exception\ApiProblemException::class);
        $this->expectExceptionMessage('Forbidden');
        $this->expectExceptionCode(403);
        $firewallMiddleware($request, $next);
    }
}
