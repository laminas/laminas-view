<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class AggregateResolverTest extends TestCase
{
    public function testAggregateIsEmptyByDefault(): void
    {
        $resolver = new AggregateResolver();
        self::assertCount(0, $resolver);
    }

    public function testCanAttachResolvers(): void
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver());
        self::assertCount(1, $resolver);
        $resolver->attach(new TemplateMapResolver());
        self::assertCount(2, $resolver);

        self::assertContainsOnlyInstancesOf(TemplateMapResolver::class, iterator_to_array($resolver));
    }

    public function testSuccessfulResolution(): void
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver([
            'foo' => 'bar',
        ]));
        $resolver->attach(new TemplateMapResolver([
            'bar' => 'baz',
        ]));

        self::assertEquals('baz', $resolver->resolve('bar'));
    }

    public function testExceptionThrownWhenNoResolverSucceeds(): void
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver([
            'foo' => 'bar',
        ]));
        self::assertFalse($resolver->resolve('bar'));
    }

    public function testResolvesInOrderOfPriorityProvided(): void
    {
        $resolver    = new AggregateResolver();
        $fooResolver = new TemplateMapResolver([
            'bar' => 'foo',
        ]);
        $barResolver = new TemplateMapResolver([
            'bar' => 'bar',
        ]);
        $bazResolver = new TemplateMapResolver([
            'bar' => 'baz',
        ]);
        $resolver->attach($fooResolver, -1)
                 ->attach($barResolver, 100)
                 ->attach($bazResolver);

        self::assertSame('bar', $resolver->resolve('bar'));
    }

    public function testExceptionThrownWhenAttemptingToResolveWhenNoResolversAreAttached(): void
    {
        $resolver = new AggregateResolver();
        self::assertFalse($resolver->resolve('foo'));
    }

    public function testResolversCanBeSuppliedInTheConstructor(): void
    {
        $fooResolver = new TemplateMapResolver([
            'bar' => 'foo',
        ]);
        $barResolver = new TemplateMapResolver([
            'bar' => 'bar',
        ]);

        $resolver = new AggregateResolver([
            $barResolver,
            $fooResolver,
        ]);

        self::assertSame('bar', $resolver->resolve('bar'));
    }

    public function testConstructorResolversAreResolvedInFIFOOrder(): void
    {
        $a = new TemplateMapResolver([
            'bar' => '1',
        ]);
        $b = new TemplateMapResolver([
            'bar' => '2',
        ]);
        $c = new TemplateMapResolver([
            'bar' => '3',
        ]);

        $resolver = new AggregateResolver([$c, $b, $a]);

        self::assertSame('3', $resolver->resolve('bar'));
    }
}
