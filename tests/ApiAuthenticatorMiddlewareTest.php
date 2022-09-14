<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Middleware\ApiAuthenticatorMiddleware;
use Waglpz\Webapp\Security\ApiBasicAuthenticator;
use Waglpz\Webapp\Security\AuthStorageInMemory;
use Waglpz\Webapp\Security\InMemoryUserAuthData;

final class ApiAuthenticatorMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new AuthStorageInMemory())->reset();
    }

    /** @test */
    public function itHasACorrectBehaviourForKnownUser(): void
    {
        $authStorage = new AuthStorageInMemory();

        $authData                        = [
            [
                'username'     => 'tester@testing',
                'role'         => 'ROLE_TEST',
                'passwordHash' => '$2y$10$tJO/FTD2bHwVMQ2qolIT9.zs31ixnjStHiHfgn8dN/aGI/tBjP6Jm',
            ],
        ];
        $authDataAdapter                 = new InMemoryUserAuthData($authData);
        $authenticator                   = new ApiBasicAuthenticator($authDataAdapter);
        $apiBasicAuthenticatorMiddleware = new ApiAuthenticatorMiddleware($authenticator, $authStorage);

        $request     = $this->createMock(ServerRequestInterface::class);
        $requestData = [
            'PHP_AUTH_USER' => 'tester@testing',
            'PHP_AUTH_PW' => 'password',
        ];
        $request->expects(self::once())->method('getServerParams')->willReturn($requestData);
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;
        $apiBasicAuthenticatorMiddleware($request, $next);

        self::assertSame('tester@testing', $authStorage->email);
    }

    /** @test */
    public function itThrownUnauthorizedApiProblemExceptionIfUserNotAuthenticated(): void
    {
        $authStorage = new AuthStorageInMemory();

        $authData                        = [
            [
                'username'     => 'tester@testing',
                'role'         => 'ROLE_TEST',
                'passwordHash' => '$2y$10$tJO/FTD2bHwVMQ2qolIT9.zs31ixnjStHiHfgn8dN/aGI/tBjP6Jm',
            ],
        ];
        $authDataAdapter                 = new InMemoryUserAuthData($authData);
        $authenticator                   = new ApiBasicAuthenticator($authDataAdapter);
        $apiBasicAuthenticatorMiddleware = new ApiAuthenticatorMiddleware($authenticator, $authStorage);

        $request     = $this->createMock(ServerRequestInterface::class);
        $requestData = [
            'PHP_AUTH_USER' => 'tester@testing',
            'PHP_AUTH_PW' => 'wrong',
        ];
        $request->expects(self::once())->method('getServerParams')->willReturn($requestData);
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;

        $this->expectException(\Phpro\ApiProblem\Exception\ApiProblemException::class);
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);

        $apiBasicAuthenticatorMiddleware($request, $next);
    }
}
