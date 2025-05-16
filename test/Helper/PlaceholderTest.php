<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Position;
use PHPUnit\Framework\TestCase;

final class PlaceholderTest extends TestCase
{
    private Placeholder $placeholder;

    protected function setUp(): void
    {
        $this->placeholder = new Placeholder();
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->placeholder, $this->placeholder->__invoke());
    }

    public function testNameIsPersistedBetweenConsecutiveCalls(): void
    {
        $value = $this->placeholder->__invoke('foo')
            ->set('a')
            ->append('b')
            ->prepend('c')
            ->setSeparator(' ')
            ->__toString();

        self::assertSame('c a b', $value);
    }

    public function testEmptyContainerYieldsEmptyString(): void
    {
        self::assertSame('', $this->placeholder->toString('foo'));
    }

    public function testTheCurrentContainerMustBeKnown(): void
    {
        $this->expectException(RuntimeException::class);
        $this->placeholder->toString();
    }

    public function testMultiplePlaceholdersCanHaveDifferentValues(): void
    {
        $this->placeholder->append('Fred', 'a')
            ->append('Wilma', 'b');

        self::assertSame('Fred', $this->placeholder->toString('a'));
        self::assertSame('Wilma', $this->placeholder->toString('b'));
    }

    public function testSetIsDestructive(): void
    {
        $value = (string) $this->placeholder->__invoke('Muppets')
            ->append('Miss Piggy')
            ->append('Fozzy Bear')
            ->set('Kermit');

        self::assertSame('Kermit', $value);
    }

    public function testContainerExists(): void
    {
        self::assertFalse($this->placeholder->containerExists('foo'));
        $this->placeholder->append('foo', 'bar');
        self::assertTrue($this->placeholder->containerExists('bar'));
    }

    public function testCaptureToSingleContainer(): void
    {
        $this->placeholder->__invoke('muppets')
            ->captureStart();

        echo 'Kermit';

        $this->placeholder->captureEnd();

        self::assertSame('Kermit', $this->placeholder->toString());
    }

    public function testCaptureToMultipleContainers(): void
    {
        $this->placeholder->captureStart('a');
        echo 'Foo';
        $this->placeholder->captureStart('b');
        echo 'Bar';
        $this->placeholder->captureEnd('b');
        $this->placeholder->captureEnd('a');

        self::assertSame('Foo', $this->placeholder->toString('a'));
        self::assertSame('Bar', $this->placeholder->toString('b'));
    }

    public function testCapturesAreAbandonedWhenTheHelperIsReset(): void
    {
        $this->placeholder->captureStart('a');
        echo 'Foo';
        $this->placeholder->captureStart('b');
        echo 'Bar';

        $this->placeholder->resetState();

        self::assertSame('', $this->placeholder->toString('a'));
        self::assertSame('', $this->placeholder->toString('b'));

        // And capturing can continue…

        $this->placeholder->captureStart('a');
        echo 'Foo';
        $this->placeholder->captureEnd('a');
        self::assertSame('Foo', $this->placeholder->toString('a'));
    }

    public function testCaptureWithSet(): void
    {
        $this->placeholder->append('append', 'a');
        $this->placeholder->captureStart('a', Position::Set);
        echo 'Foo';
        $this->placeholder->captureEnd();

        self::assertSame('Foo', $this->placeholder->toString());
    }

    public function testCaptureWithPrepend(): void
    {
        $this->placeholder->append('append', 'a');
        $this->placeholder->captureStart('a', Position::Prepend);
        echo 'Prepend';
        $this->placeholder->captureEnd();

        self::assertSame('Prependappend', $this->placeholder->toString());
    }

    public function testCustomSeparator(): void
    {
        $value = $this->placeholder->__invoke('a')
            ->append('a')
            ->append('b')
            ->append('c')
            ->setSeparator('-')
            ->toString();

        self::assertSame('a-b-c', $value);
    }
}
