<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateCannotBeFound;
use Laminas\View\Resolver\TemplateMapResolver;
use PHPUnit\Framework\TestCase;

final class AggregateResolverTest extends TestCase
{
    public function testAggregateIsEmptyByDefault(): void
    {
        $resolver = new AggregateResolver();
        $this->assertCount(0, $resolver);
    }

    public function testCanAttachResolvers(): void
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver());
        $this->assertCount(1, $resolver);
        $resolver->attach(new TemplateMapResolver());
        $this->assertCount(2, $resolver);

        self::assertContainsOnlyInstancesOf(TemplateMapResolver::class, $resolver);
    }

    public function testReturnsNonFalseValueWhenAtLeastOneResolverSucceeds(): void
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver([
            'foo' => 'bar',
        ]));
        $resolver->attach(new TemplateMapResolver([
            'bar' => 'baz',
        ]));
        $test = $resolver->resolve('bar');
        $this->assertEquals('baz', $test);
    }

    public function testExceptionThrownWhenNoResolverSucceeds(): void
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver([
            'foo' => 'bar',
        ]));
        $this->expectException(TemplateCannotBeFound::class);
        $resolver->resolve('bar');
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

        $test = $resolver->resolve('bar');
        $this->assertEquals('bar', $test);
    }

    public function testExceptionThrownWhenAttemptingToResolveWhenNoResolversAreAttached(): void
    {
        $resolver = new AggregateResolver();
        $this->expectException(TemplateCannotBeFound::class);
        $resolver->resolve('foo');
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
