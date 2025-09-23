<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Placeholder;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Placeholder\Container;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;
use function ob_end_clean;

final class ContainerTest extends TestCase
{
    public function testInitialState(): void
    {
        $container = new Container();
        self::assertSame([], $container->toArray());
        self::assertCount(0, $container);
        self::assertSame([], iterator_to_array($container));
        self::assertFalse($container->isCapturing());
    }

    public function testItemsCanBeAppended(): void
    {
        $container = new Container();
        $container->append('foo');
        $container->append('bar');

        self::assertSame(['foo', 'bar'], $container->toArray());
    }

    public function testItemsCanBePrepended(): void
    {
        $container = new Container();
        $container->prepend('foo');
        $container->prepend('bar');

        self::assertSame(['bar', 'foo'], $container->toArray());
    }

    public function testSetOverwritesItems(): void
    {
        $container = new Container();
        $container->prepend('foo');
        $container->prepend('bar');
        $container->set('baz');

        self::assertSame(['baz'], $container->toArray());
    }

    public function testItKnowsHowToCount(): void
    {
        $container = new Container();
        $container->prepend('foo');
        $container->prepend('bar');

        self::assertCount(2, $container);
    }

    public function testItIsIterable(): void
    {
        $container = new Container();
        $container->prepend('foo');
        $container->prepend('bar');

        self::assertSame(['bar', 'foo'], iterator_to_array($container));
    }

    public function testIsCapturingIsTrueWhenCapturing(): void
    {
        $container = new Container();
        $container->captureStart();
        self::assertTrue($container->isCapturing());
        $container->captureEnd();
    }

    public function testContentIsCaptured(): void
    {
        $container = new Container();
        $container->captureStart();
        echo 'Foo';
        $content = $container->captureEnd();
        self::assertSame('Foo', $content);
    }

    public function testMultipleContainersCanCaptureContent(): void
    {
        $a = new Container();
        $b = new Container();
        $a->captureStart();
        echo 'A';
        $b->captureStart();
        echo 'B';
        self::assertSame('B', $b->captureEnd());
        self::assertSame('A', $a->captureEnd());
    }

    public function testExceptionThrownAttemptingToStartCaptureTwice(): void
    {
        $container = new Container();
        $container->captureStart();

        try {
            $container->captureStart();
            self::fail('An exception was expected');
        } catch (RuntimeException $e) {
            self::assertEquals('This container is already capturing output', $e->getMessage());
        } finally {
            ob_end_clean();
        }
    }

    public function testExceptionThrownEndingCaptureWhenItHasNotBeenStarted(): void
    {
        $container = new Container();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This container is not currently capturing output');

        $container->captureEnd();
    }

    public function testRemove(): void
    {
        $container = new Container();
        $container->append('a');
        $container->append('b');
        $container->remove('b');
        self::assertSame(['a'], $container->toArray());
    }

    public function testFirstMatch(): void
    {
        $a = (object) ['prop' => 1];
        $b = (object) ['prop' => 2];
        $c = (object) ['prop' => 1];

        /** @psalm-var Container<object{prop: int}> $container */
        $container = new Container();
        $container->append($a);
        $container->append($b);
        $container->append($c);

        $item = $container->firstMatch(static fn (object $o): bool => $o->prop === 1);
        self::assertSame($a, $item);
    }

    public function testFirstMatchIsNullWhenThereIsNoMatch(): void
    {
        /** @psalm-var Container<string> $container */
        $container = new Container();
        $container->append('a');
        $container->append('b');

        self::assertNull(
            $container->firstMatch(static fn (string $i): bool => $i === 'z'),
        );
    }
}
