<?php

namespace Test;

use Kovagoz\Http\Middleware\RequestDispatcher\ContainerResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ContainerResolverTest extends TestCase
{
    /**
     * When everything goes fine.
     */
    public function testResolveHandlerSuccessfully(): void
    {
        // The request handler should be returned
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects(self::once())->method('get')->willReturn($handler);

        $resolver = new ContainerResolver($container);

        self::assertSame($handler, $resolver->resolve('ValidHandlerIdentifier'));
    }

    /**
     * When resolver gets a handler ID which cannot be casted to string.
     *
     * @param mixed $handlerId
     * @dataProvider invalidHandlerIdentifierDataProvider
     */
    public function testInvalidHandlerIdentifierType($handlerId): void
    {
        $this->expectException(\TypeError::class);

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects(self::never())->method('get');

        $resolver = new ContainerResolver($container);
        $resolver->resolve($handlerId);
    }

    public function invalidHandlerIdentifierDataProvider(): \Generator
    {
        yield [new \stdClass()];
        yield [array()];
    }

    /**
     * Handler must be instance of \Psr\Http\Server\RequestHandlerInterface
     */
    public function testInvalidHandlerType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Container returns with string which is not a valid handler type
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects(self::once())->method('get')->willReturn('foo');

        $resolver = new ContainerResolver($container);
        $resolver->resolve('ValidHandlerIdentifier');
    }

    /**
     * When the DI container can't make the requested object.
     */
    public function testHandlerCannotBeResolvedByContainer(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $notFoundException = new class extends \Exception implements NotFoundExceptionInterface {};

        // Container returns with string "foo" which is not a valid handler
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects(self::once())->method('get')->willThrowException($notFoundException);

        $resolver = new ContainerResolver($container);
        $resolver->resolve('ValidHandlerIdentifier');
    }
}
