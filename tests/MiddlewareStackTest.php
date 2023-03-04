<?php

declare(strict_types=1);

namespace Waglpz\Webapp\Middleware\Tests;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Waglpz\Webapp\Middleware\Middleware;
use Waglpz\Webapp\Middleware\MiddlewareStack;

final class MiddlewareStackTest extends TestCase
{
    /**
     * @param array<int,string> $middlewares
     * @param array<int,string> $expectation
     *
     * @throws Exception
     *
     * @test
     * @dataProvider dataForTest
     */
    public function itHasACorrectBehaviour(array $middlewares, array $expectation): void
    {
        $preserver   = new \ArrayObject();
        $middlewares = \array_map(fn ($id) => $this->createAMiddleware($id, $preserver), $middlewares);
        $finalist    = $this->createLastMiddleware($preserver);

        $middlewareStack = new MiddlewareStack($middlewares, $finalist);
        $request         = $this->createMock(ServerRequestInterface::class);
        $response        = $middlewareStack($request);
        $fact            = $response->getBody();
        self::assertSame('finalist was called', (string) $fact);
        self::assertEquals($expectation, $preserver->getArrayCopy());
    }

    public static function dataForTest(): \Generator
    {
        yield 'Manual fixed order middlewares will be executed in right order' => [
            // manual created index for sorting middlewares started from 1
            [
                1 => 'middleware_n1_tbd_as_1',
                4 => 'middleware_n2_tbd_as_4',
                3 => 'middleware_n3_tbd_as_3',
                2 => 'middleware_n4_tbd_as_2',
            ],
            [
                'middleware_n1_tbd_as_1',
                'middleware_n4_tbd_as_2',
                'middleware_n3_tbd_as_3',
                'middleware_n2_tbd_as_4',
                'last',
            ],
        ];

        yield 'Auto ordered middlewares will be executed in same order' => [
            // auto created index on the fly started from 0
            [
                'middleware_tbd_as_1',
                'middleware_tbd_as_2',
                'middleware_tbd_as_3',
                'middleware_tbd_as_4',
            ],
            [
                'middleware_tbd_as_1',
                'middleware_tbd_as_2',
                'middleware_tbd_as_3',
                'middleware_tbd_as_4',
                'last',
            ],
        ];
    }

    /** @param \ArrayObject<int,string> $preserver */
    private function createAMiddleware(string $identifier, \ArrayObject $preserver): Middleware
    {
        return new class ($identifier, $preserver) implements Middleware {
            /** @param \ArrayObject<int,string> $preserver */
            public function __construct(
                private readonly string $identifier,
                private readonly \ArrayObject $preserver,
            ) {
            }

            public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
            {
                $this->preserver->append($this->identifier);

                return $next($request);
            }
        };
    }

    /**
     * @param \ArrayObject<int,string> $preserver
     *
     * @throws Exception
     */
    private function createLastMiddleware(\ArrayObject $preserver): callable
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('getBody')->willReturnCallback(
            static function () {
                return 'finalist was called';
            },
        );

        return new class ($response, $preserver) {
            /** @param \ArrayObject<int,string> $preserver */
            public function __construct(
                private readonly ResponseInterface $response,
                private readonly \ArrayObject $preserver,
            ) {
            }

            public function __invoke(): ResponseInterface
            {
                $this->preserver->append('last');

                return $this->response;
            }
        };
    }
}
