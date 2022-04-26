<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Security\AuthStorage;
use Waglpz\Webapp\Security\UserRolesProvider;

final class UserRolesMiddleware implements Middleware
{
    private AuthStorage $authStorage;
    private UserRolesProvider $rolesProvider;

    public function __construct(
        UserRolesProvider $rolesProvider,
        AuthStorage $authStorage
    ) {
        $this->authStorage   = $authStorage;
        $this->rolesProvider = $rolesProvider;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $username = $this->authStorage->email ?? '';

        $this->authStorage->roles = $this->rolesProvider->findRole($username);

        return $next($request);
    }
}
