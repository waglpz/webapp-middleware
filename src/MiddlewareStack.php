<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MiddlewareStack
{
    private \Closure $middleware;

    /** @param array<Middleware|callable> $middleware */
    public function __construct(array $middleware, callable $finalistCaller)
    {
        \krsort($middleware);

        $middleware = \array_reduce(
            $middleware,
            static fn ($car, callable $cur) => static fn (ServerRequestInterface $request) => $cur($request, $car),
            $finalistCaller
        );
        \assert($middleware instanceof \Closure);

        $this->middleware = $middleware;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->middleware)($request);
    }
}
