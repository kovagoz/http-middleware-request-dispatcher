<?php

namespace Kovagoz\Http\Middleware\RequestDispatcher;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Instantiates request handler objects using a DI container.
 *
 * @see \Test\ContainerResolverTest
 */
class ContainerResolver implements RequestHandlerResolver
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolve(string $handlerIdentifier): RequestHandlerInterface
    {
        try {
            $handler = $this->container->get($handlerIdentifier);
        } catch (NotFoundExceptionInterface $exception) {
            throw new \InvalidArgumentException('Cannot resolve route handler', 0, $exception);
        }

        if ($handler instanceof RequestHandlerInterface) {
            return $handler;
        }

        throw new \InvalidArgumentException(
            'Route handler is not instance of RequestHandlerInterface'
        );
    }
}
