<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Security\AuthStorage;
use Waglpz\Webapp\Security\CredentialDataAdapter;

final class UserSpacesMiddleware implements Middleware
{
    public function __construct(
        private readonly AuthStorage $authStorage,
        private readonly CredentialDataAdapter $credentialDataAdapter,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $credentialsData = $this->credentialDataAdapter->fetch();

        if ($credentialsData !== null) {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->authStorage->spaces = $credentialsData->spaces(); /* @phpstan-ignore-line */
        } else {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->authStorage->spaces = []; /* @phpstan-ignore-line */
        }

        return $next($request);
    }
}
