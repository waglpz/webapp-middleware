<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Phpro\ApiProblem\Exception\ApiProblemException;
use Phpro\ApiProblem\Http\UnauthorizedProblem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Security\Authenticator;
use Waglpz\Webapp\Security\AuthStorage;

final class ApiAuthenticatorMiddleware implements Middleware
{
    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly AuthStorage $authStorage,
    ) {
    }

    /** @throws ApiProblemException */
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        if (! $this->authenticator->authenticate($request)) {
            $apiProblem = new UnauthorizedProblem('Unauthorized');

            throw new ApiProblemException($apiProblem);
        }

        $this->authStorage->reset();

        $this->authStorage->email = $this->authenticator->username() ?? '';

        return $next($request);
    }
}
