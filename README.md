# PSR-15 compatible request dispatcher

The purpose of this middleware is to dispatch the request to the assigned
handler, also known as the controller.

## Requirements

* PHP 7.4 or above

## Usage

Middleware needs a _resolver_ object to be able to make the proper
request handler object. It must be injected through the constructor.
This package contains a simple resolver implementation which uses a PSR-11
compatible DI container to do the job:

```php
$resolver   = new ContainerResolver($container);
$middleware = new RequestDispatcher($resolver);
```

Middleware looks for a special attribute of the request object called *__handler*.
This attribute tells which handler should handle the request. If it's missing,
nothing happens, request will be passed to the next middleware.

Here an example how you can tell which handler should handle the request:

```php
// PSR-7 server request object
$request = $request->withAttribute('__handler', MyController::class);
$middleware->process($request, $nextMiddleware);
```
