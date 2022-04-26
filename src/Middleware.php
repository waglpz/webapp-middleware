<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Middleware
{
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface;
}
