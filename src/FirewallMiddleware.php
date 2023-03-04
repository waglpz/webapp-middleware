<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Phpro\ApiProblem\Exception\ApiProblemException;
use Phpro\ApiProblem\Http\ForbiddenProblem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Security\AuthStorage;
use Waglpz\Webapp\Security\Firewalled;
use Waglpz\Webapp\Security\Forbidden;

final class FirewallMiddleware implements Middleware
{
    public function __construct(
        private readonly Firewalled $firewall,
        private readonly AuthStorage $authStorage,
    ) {
    }

    /** @throws ApiProblemException */
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        try {
            $this->firewall->checkRules($request, $this->authStorage->roles);
        } catch (Forbidden $exception) {
            $apiProblem = new ForbiddenProblem($exception->getMessage());

            throw new ApiProblemException($apiProblem);
        }

        return $next($request);
    }
}
