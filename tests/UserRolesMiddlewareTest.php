<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Middleware\UserRolesMiddleware;
use Waglpz\Webapp\Security\AuthStorage;
use Waglpz\Webapp\Security\InMemoryUserAuthData;
use Waglpz\Webapp\Security\UserAuthRolesProvider;

final class UserRolesMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new AuthStorage())->reset();
    }

    /** @test */
    public function itHasACorrectBehaviourForKnownUser(): void
    {
        $authStorage        = new AuthStorage();
        $authStorage->email = 'tester@testing';

        $authData           = [
            [
                'username'     => 'tester@testing',
                'role'         => 'ROLE_TEST',
                'passwordHash' => 'xxx',
            ],
        ];
        $authDataAdapter    = new InMemoryUserAuthData($authData);
        $rolesProvider      = new UserAuthRolesProvider($authDataAdapter);
        $usrRolesMiddleware = new UserRolesMiddleware($rolesProvider, $authStorage);

        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;
        $usrRolesMiddleware($request, $next);

        self::assertSame(['ROLE_TEST'], $authStorage->roles);
    }

    /** @test */
    public function itHasACorrectBehaviourForUnknownUser(): void
    {
        $authStorage        = new AuthStorage();
        $authStorage->email = 'tester+1@testing';

        $authData           = [
            [
                'username'     => 'tester@testing',
                'role'         => 'ROLE_TEST',
                'passwordHash' => 'xxx',
            ],
        ];
        $authDataAdapter    = new InMemoryUserAuthData($authData);
        $rolesProvider      = new UserAuthRolesProvider($authDataAdapter);
        $usrRolesMiddleware = new UserRolesMiddleware($rolesProvider, $authStorage);

        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;
        $usrRolesMiddleware($request, $next);

        self::assertSame(['ROLE_NOT_AUTHENTICATED'], $authStorage->roles);
    }

    /** @test */
    public function itHasACorrectBehaviourFromScratch(): void
    {
        $authStorage        = new AuthStorage();
        $authData           = [];
        $authDataAdapter    = new InMemoryUserAuthData($authData);
        $rolesProvider      = new UserAuthRolesProvider($authDataAdapter);
        $usrRolesMiddleware = new UserRolesMiddleware($rolesProvider, $authStorage);

        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;
        $usrRolesMiddleware($request, $next);

        self::assertSame(['ROLE_NOT_AUTHENTICATED'], $authStorage->roles);
    }
}
