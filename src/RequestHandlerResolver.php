<?php

namespace Kovagoz\Http\Middleware\RequestDispatcher;

use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerResolver
{
    public function resolve(string $handlerIdentifier): RequestHandlerInterface;
}
