<?php

namespace Kovagoz\Http\Middleware\RequestDispatcher;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
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
            throw new ResolverException('Request handler is not found in the container', 0, $exception);
        }

        if ($handler instanceof RequestHandlerInterface) {
            return $handler;
        }

        throw new ResolverException(
            'Request handler is not instance of ' . ServerRequestInterface::class
        );
    }
}
