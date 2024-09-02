<?php

declare(strict_types=1);

use Dice\Dice;
use Waglpz\Webapp\Middleware\ApiAuthenticatorMiddleware;
use Waglpz\Webapp\Middleware\FirewallMiddleware;
use Waglpz\Webapp\Middleware\MiddlewareStackFactory;
use Waglpz\Webapp\Middleware\UserRolesMiddleware;
use Waglpz\Webapp\Middleware\UserSpacesMiddleware;

return [
    MiddlewareStackFactory::class                       => [
        'shared'          => true,
        'constructParams' => [
            [
                [Dice::INSTANCE => ApiAuthenticatorMiddleware::class],
                [Dice::INSTANCE => UserRolesMiddleware::class],
                [Dice::INSTANCE => UserSpacesMiddleware::class],
                [Dice::INSTANCE => FirewallMiddleware::class],
            ],
        ],
    ],
];
