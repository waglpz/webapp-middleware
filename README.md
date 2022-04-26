Waglpz WebApp Middleware Component
================================

![PHP Checked](https://github.com/waglpz/webapp-middleware/workflows/PHP%20Composer/badge.svg)

Install via composer
--------------------

`composer require waglpz/webapp-middleware`

Working with sources within Docker
----------------------------------

Clone Project in some Directory `git clone https://github.com/waglpz/webapp-middleware.git` 

Go into Directory `webapp-middleware` and run: `bash ./bin/start.sh` to start working within Docker Container.

To stop and clean run: `bash ./bin/clean.sh`

##### Composer using from Docker Container
 1. Install Vendor Dependencies `composer install`
 2. Display Waglpz Composer commands: `composer list | grep waglpz`
    1. Check Source Code vitality: `composer waglpz:check:normal` 
    1. Check Source Code Styles: `waglpz:cs-check`
    1. Automatic fix Source Code Styles Errors: `waglpz:cs-fix`

#### Create and Call Middleware Stack

Example PHP code
```php
$request;
\assert($request instanceof \Psr\Http\Message\ServerRequestInterface);

$middleware_1;
\assert($middleware_1 instanceof Waglpz\Webapp\Middleware\Middleware);
$middleware_2;
\assert($middleware_2 instanceof Waglpz\Webapp\Middleware\Middleware);
$middleware_3;
\assert($middleware_3 instanceof Waglpz\Webapp\Middleware\Middleware);
$finnaly;
\assert(\is_callable($finnaly));
$middlewares = [
    $middleware_1,// execute first
    $middleware_2,// execute second
    $middleware_3 // executes third
];


$middlewareStack = new Waglpz\Webapp\Middleware\MiddlewareStack($middlewares);

$response = $middlewareStack($request);
// or exact same as manual call
$response = $middleware_1(
    $request, 
    fn ($request) => $middleware_2(
        $request, 
        fn ($request) => $middleware_3(
            $request, 
            fn ($request) => $finnaly(
                $request
            )
        )
    )
); 

\assert($response instanceof \Psr\Http\Message\ResponseInterface)
```
