<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MiddlewareStackFactory
{
    /** @var callable|null */
    private $middlewareStack = null;

    /** @param array<Middleware|callable> $middlewares */
    public function __construct(private array $middlewares)
    {
    }

    public function create(callable $finalHandler): MiddlewareStackFactory
    {
        \krsort($this->middlewares);
        $newSelf = new self($this->middlewares);

        $newSelf->middlewareStack = \array_reduce(
            $this->middlewares,
            static fn ($car, callable $cur) => static fn (ServerRequestInterface $request) => $cur($request, $car),
            $finalHandler,
        );
        \assert($newSelf->middlewareStack instanceof \Closure);

        return $newSelf;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->middlewareStack === null) {
            throw new \LogicException('Middleware Stack was not properly created. Please use Create Method.');
        }

        return ($this->middlewareStack)($request);
    }
}
