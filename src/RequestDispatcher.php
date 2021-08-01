<?php

namespace Kovagoz\Http\Middleware\RequestDispatcher;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * @see \Test\RequestDispatcherTest
 */
class RequestDispatcher implements MiddlewareInterface
{
    public const FORWARD_HEADER    = 'x-forward';
    public const HANDLER_ATTRIBUTE = '__handler';

    private RequestHandlerResolver $resolver;

    public function __construct(RequestHandlerResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $requestHandler = $request->getAttribute(self::HANDLER_ATTRIBUTE);

        // No handler defined, skip this middleware
        if ($requestHandler === null) {
            return $handler->handle($request);
        }

        do {
            // Internal redirect happened
            if (isset($response)) {
                $requestHandler = current($response->getHeader(self::FORWARD_HEADER));
            }

            $handler  = $this->resolver->resolve($requestHandler);
            $response = $handler->handle($request);
        } while ($response->hasHeader(self::FORWARD_HEADER));

        return $response;
    }
}
