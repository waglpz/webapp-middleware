<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Phpro\ApiProblem\Exception\ApiProblemException;
use Phpro\ApiProblem\Http\UnauthorizedProblem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Security\ApiBasicAuthenticator;
use Waglpz\Webapp\Security\AuthStorage;

final class ApiBasicAuthenticatorMiddleware implements Middleware
{
    private AuthStorage $authStorage;
    private ApiBasicAuthenticator $apiBasicAuthenticator;

    public function __construct(
        ApiBasicAuthenticator $apiBasicAuthenticator,
        AuthStorage $authStorage
    ) {
        $this->authStorage           = $authStorage;
        $this->apiBasicAuthenticator = $apiBasicAuthenticator;
    }

    /** @throws ApiProblemException */
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        if (! $this->apiBasicAuthenticator->authenticate($request)) {
            $apiProblem = new UnauthorizedProblem('Unauthorized');

            throw new ApiProblemException($apiProblem);
        }

        $this->authStorage->reset();
        $this->authStorage->email = $this->apiBasicAuthenticator->username() ?? '';

        return $next($request);
    }
}
