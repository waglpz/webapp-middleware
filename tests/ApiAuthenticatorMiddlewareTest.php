<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware\Tests;

use Phpro\ApiProblem\Exception\ApiProblemException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Middleware\ApiAuthenticatorMiddleware;
use Waglpz\Webapp\Security\AuthenticatorBasic;
use Waglpz\Webapp\Security\AuthStorageInMemory;
use Waglpz\Webapp\Security\CredentialDataAdapterInMemory;
use Waglpz\Webapp\Security\CredentialDataDecoderInMemoryDefault;

final class ApiAuthenticatorMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new AuthStorageInMemory())->reset();
    }

    /**
     * @throws Exception|ApiProblemException
     *
     * @test
     */
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
        $decoder                         = new CredentialDataDecoderInMemoryDefault();
        $authDataAdapter                 = new CredentialDataAdapterInMemory($authData, $decoder);
        $authenticator                   = new AuthenticatorBasic($authDataAdapter);
        $apiBasicAuthenticatorMiddleware = new ApiAuthenticatorMiddleware($authenticator, $authStorage);

        $request     = $this->createMock(ServerRequestInterface::class);
        $requestData = [
            'PHP_AUTH_USER' => 'tester@testing',
            'PHP_AUTH_PW'   => 'password',
        ];
        $request->expects(self::once())->method('getServerParams')->willReturn($requestData);
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;
        $apiBasicAuthenticatorMiddleware($request, $next);

        self::assertSame('tester@testing', $authStorage->email);
    }

    /**
     * @throws Exception
     *
     * @test
     */
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
        $decoder                         = new CredentialDataDecoderInMemoryDefault();
        $authDataAdapter                 = new CredentialDataAdapterInMemory($authData, $decoder);
        $authenticator                   = new AuthenticatorBasic($authDataAdapter);
        $apiBasicAuthenticatorMiddleware = new ApiAuthenticatorMiddleware($authenticator, $authStorage);

        $request     = $this->createMock(ServerRequestInterface::class);
        $requestData = [
            'PHP_AUTH_USER' => 'tester@testing',
            'PHP_AUTH_PW'   => 'wrong',
        ];
        $request->expects(self::once())->method('getServerParams')->willReturn($requestData);
        $response = $this->createMock(ResponseInterface::class);
        $next     = static fn (ServerRequestInterface $request) => $response;

        $this->expectException(ApiProblemException::class);
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);

        $apiBasicAuthenticatorMiddleware($request, $next);
    }
}
