<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Middleware\Middleware;
use Waglpz\Webapp\Middleware\MiddlewareStack;

final class MiddlewareStackTest extends TestCase
{
    /** @var \ArrayObject<int, int|string> */
    public \ArrayObject $p;

    /** @test */
    public function itHasACorrectBehaviour(): void
    {
        $this->p = new \ArrayObject();

        $middlewares = [
            $this->createAMiddleware(1),
            $this->createAMiddleware(2),
            $this->createAMiddleware(3),
        ];

        $finalist = $this->createLastMiddleware();
        $r        = new MiddlewareStack($middlewares, $finalist);
        $request  = $this->createMock(ServerRequestInterface::class);

        $response = $r($request);
        $fact     = $response->getBody();
        \assert($fact instanceof \ArrayObject);

        self::assertEquals([1, 2, 3, 'last'], $fact->getArrayCopy());
    }

    private function createAMiddleware(int $identifier): Middleware
    {
        $holder = $this->p;

        return new class ($identifier, $holder) implements Middleware {
            private int $identifier;
            /** @phpstan-ignore-next-line */
            private \ArrayObject $holder;

            /** @param \ArrayObject<int, int|string> $holder */
            public function __construct(int $identifier, \ArrayObject $holder)
            {
                $this->identifier = $identifier;
                $this->holder     = $holder;
            }

            public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
            {
                $this->holder[] = $this->identifier;

                return $next($request);
            }
        };
    }

    private function createLastMiddleware(): callable
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('getBody')->willReturnCallback(
            function () {
                $this->p[] = 'last';

                return $this->p;
            }
        );

        return new class ($response) {
            /** @var ResponseInterface|MockObject */
            private $response;

            /** @param ResponseInterface|MockObject $response */
            public function __construct($response)
            {
                $this->response = $response;
            }

            public function __invoke(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
