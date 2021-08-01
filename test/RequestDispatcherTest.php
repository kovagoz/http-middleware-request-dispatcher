<?php

namespace Test;

use Kovagoz\Http\Middleware\RequestDispatcher\RequestDispatcher;
use Kovagoz\Http\Middleware\RequestDispatcher\RequestHandlerResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestDispatcherTest extends TestCase
{
    /**
     * When request object does not have the handler attribute.
     */
    public function testNoHandlerDefinedInRequest(): void
    {
        // Request without handler definition
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())->method('getAttribute')->willReturn(null);

        // Response from the next middleware
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        // This is the next middleware
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn($response);

        // Resolver should not be called
        $resolver = $this->getMockForAbstractClass(RequestHandlerResolver::class);
        $resolver->expects(self::never())->method('resolve');

        $middleware = new RequestDispatcher($resolver);

        // Returns with the response from the next middleware
        self::assertSame($response, $middleware->process($request, $handler));
    }

    /**
     * When request has the handler attribute but it cannot be resolved.
     */
    public function testNonexistentRequestHandler(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // In this case the resolver will throw an exception
        $resolver = $this->getMockForAbstractClass(RequestHandlerResolver::class);
        $resolver->expects(self::once())->method('resolve')
            ->willThrowException(new \InvalidArgumentException());

        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())->method('getAttribute')->willReturn('NonexistentController');

        // The next middleware in the stack should not be called
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $middleware = new RequestDispatcher($resolver);
        $middleware->process($request, $handler);
    }

    /**
     * When the instantiated request handler does not implement the required interface.
     */
    public function testImproperRequestHandler(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // In this case the resolver will throw an exception
        $resolver = $this->getMockForAbstractClass(RequestHandlerResolver::class);
        $resolver->expects(self::once())->method('resolve')
            ->willThrowException(new \InvalidArgumentException());

        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())->method('getAttribute')->willReturn('Controller');

        // The next middleware in the stack should not be called
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $middleware = new RequestDispatcher($resolver);
        $middleware->process($request, $handler);
    }

    /**
     * When request handler returns with a normal response.
     */
    public function testHandleRequest(): void
    {
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        // This is our request handler (also called a controller)
        $controller = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $controller->expects(self::once())->method('handle')->willReturn($response);

        $resolver = $this->getMockForAbstractClass(RequestHandlerResolver::class);
        $resolver->expects(self::once())->method('resolve')->with('Controller')->willReturn($controller);

        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())->method('getAttribute')->willReturn('Controller');

        // The next middleware in the stack should not be called
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $middleware = new RequestDispatcher($resolver);

        // Middleware returns with the response from our request handler
        self::assertSame($response, $middleware->process($request, $handler));
    }

    /**
     * When the response from the request handler has a forward header.
     */
    public function testForwardRequest(): void
    {
        // Response from the first handler returns with forward header
        $response1 = $this->getMockForAbstractClass(ResponseInterface::class);
        $response1->expects(self::once())
            ->method('hasHeader')
            ->with(RequestDispatcher::FORWARD_HEADER)
            ->willReturn(true);
        $response1->expects(self::once())
            ->method('getHeader')
            ->with(RequestDispatcher::FORWARD_HEADER)
            ->willReturn(['Controller2']);

        $controller1 = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $controller1->expects(self::once())->method('handle')->willReturn($response1);

        // Normal response from the second handler
        $response2 = $this->getMockForAbstractClass(ResponseInterface::class);

        $controller2 = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $controller2->expects(self::once())->method('handle')->willReturn($response2);

        // Resolver will resolve both request handlers
        $resolver = $this->getMockForAbstractClass(RequestHandlerResolver::class);
        $resolver->expects(self::exactly(2))->method('resolve')
            ->withConsecutive(['Controller1'], ['Controller2'])
            ->willReturnOnConsecutiveCalls($controller1, $controller2);

        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())->method('getAttribute')->willReturn('Controller1');

        // The next middleware in the stack should not be called
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $middleware = new RequestDispatcher($resolver);

        // Result should be the response from the second handler
        self::assertSame($response2, $middleware->process($request, $handler));
    }
}
