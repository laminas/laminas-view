<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver;

use Exception;
use Laminas\View\Resolver;
use PHPUnit\Framework\TestCase;

class AggregateResolverTest extends TestCase
{
    public function testAggregateIsEmptyByDefault(): void
    {
        $resolver = new Resolver\AggregateResolver();
        $this->assertCount(0, $resolver);
    }

    public function testCanAttachResolvers(): void
    {
        $resolver = new Resolver\AggregateResolver();
        $resolver->attach(new Resolver\TemplateMapResolver());
        $this->assertCount(1, $resolver);
        $resolver->attach(new Resolver\TemplateMapResolver());
        $this->assertCount(2, $resolver);
    }

    public function testReturnsNonFalseValueWhenAtLeastOneResolverSucceeds(): void
    {
        $resolver = new Resolver\AggregateResolver();
        $resolver->attach(new Resolver\TemplateMapResolver([
            'foo' => 'bar',
        ]));
        $resolver->attach(new Resolver\TemplateMapResolver([
            'bar' => 'baz',
        ]));
        $test = $resolver->resolve('bar');
        $this->assertEquals('baz', $test);
    }

    public function testLastSuccessfulResolverIsNullInitially(): void
    {
        $resolver = new Resolver\AggregateResolver();
        $this->assertNull($resolver->getLastSuccessfulResolver());
    }

    public function testCanAccessResolverThatLastSucceeded(): void
    {
        $resolver    = new Resolver\AggregateResolver();
        $fooResolver = new Resolver\TemplateMapResolver([
            'foo' => 'bar',
        ]);
        $barResolver = new Resolver\TemplateMapResolver([
            'bar' => 'baz',
        ]);
        $bazResolver = new Resolver\TemplateMapResolver([
            'baz' => 'bat',
        ]);
        $resolver->attach($fooResolver)
                 ->attach($barResolver)
                 ->attach($bazResolver);

        $test = $resolver->resolve('bar');
        $this->assertEquals('baz', $test);
        $this->assertSame($barResolver, $resolver->getLastSuccessfulResolver());
    }

    public function testReturnsFalseWhenNoResolverSucceeds(): void
    {
        $resolver = new Resolver\AggregateResolver();
        $resolver->attach(new Resolver\TemplateMapResolver([
            'foo' => 'bar',
        ]));
        $this->assertFalse($resolver->resolve('bar'));
        $this->assertEquals(Resolver\AggregateResolver::FAILURE_NOT_FOUND, $resolver->getLastLookupFailure());
    }

    public function testLastSuccessfulResolverIsNullWhenNoResolverSucceeds(): void
    {
        $resolver    = new Resolver\AggregateResolver();
        $fooResolver = new Resolver\TemplateMapResolver([
            'foo' => 'bar',
        ]);
        $resolver->attach($fooResolver);
        $resolver->resolve('foo');
        $this->assertSame($fooResolver, $resolver->getLastSuccessfulResolver());

        try {
            $resolver->resolve('bar');
            $this->fail('Should not have resolved!');
        } catch (Exception $e) {
            // exception is expected
        }
        $this->assertNull($resolver->getLastSuccessfulResolver());
    }

    public function testResolvesInOrderOfPriorityProvided(): void
    {
        $resolver    = new Resolver\AggregateResolver();
        $fooResolver = new Resolver\TemplateMapResolver([
            'bar' => 'foo',
        ]);
        $barResolver = new Resolver\TemplateMapResolver([
            'bar' => 'bar',
        ]);
        $bazResolver = new Resolver\TemplateMapResolver([
            'bar' => 'baz',
        ]);
        $resolver->attach($fooResolver, -1)
                 ->attach($barResolver, 100)
                 ->attach($bazResolver);

        $test = $resolver->resolve('bar');
        $this->assertEquals('bar', $test);
    }

    public function testReturnsFalseWhenAttemptingToResolveWhenNoResolversAreAttached(): void
    {
        $resolver = new Resolver\AggregateResolver();
        $this->assertFalse($resolver->resolve('foo'));
        $this->assertEquals(Resolver\AggregateResolver::FAILURE_NO_RESOLVERS, $resolver->getLastLookupFailure());
    }
}
