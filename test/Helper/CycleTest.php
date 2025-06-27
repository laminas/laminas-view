<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Cycle;
use PHPUnit\Framework\TestCase;

final class CycleTest extends TestCase
{
    private Cycle $helper;

    protected function setUp(): void
    {
        $this->helper = new Cycle();
    }

    public function testCycleMethodReturnsObjectInstance(): void
    {
        self::assertSame(
            $this->helper,
            $this->helper->__invoke(),
        );
    }

    public function testAssignAndGetValues(): void
    {
        $this->helper->assign(['a', 1, 'asd']);
        self::assertEquals(['a', 1, 'asd'], $this->helper->getAll());
    }

    public function testCycleMethod(): void
    {
        $this->helper->__invoke(['a', 1, 'asd']);
        self::assertEquals(['a', 1, 'asd'], $this->helper->getAll());
    }

    public function testToString(): void
    {
        $this->helper->__invoke(['a', 1, 'asd']);
        self::assertEquals('a', $this->helper->toString());
    }

    public function testNextValue(): void
    {
        $this->helper->assign(['a', 1, 3]);
        self::assertEquals('a', (string) $this->helper->next());
        self::assertEquals(1, (string) $this->helper->next());
        self::assertEquals(3, (string) $this->helper->next());
        self::assertEquals('a', (string) $this->helper->next());
        self::assertEquals(1, (string) $this->helper->next());
    }

    public function testPrevValue(): void
    {
        $this->helper->assign([4, 1, 3]);
        self::assertEquals(3, (string) $this->helper->prev());
        self::assertEquals(1, (string) $this->helper->prev());
        self::assertEquals(4, (string) $this->helper->prev());
        self::assertEquals(3, (string) $this->helper->prev());
        self::assertEquals(1, (string) $this->helper->prev());
    }

    public function testRewind(): void
    {
        $this->helper->assign([5, 8, 3]);
        self::assertEquals(5, (string) $this->helper->next());
        self::assertEquals(8, (string) $this->helper->next());
        $this->helper->rewind();
        self::assertEquals(5, (string) $this->helper->next());
        self::assertEquals(8, (string) $this->helper->next());
    }

    public function testMixedMethods(): void
    {
        $this->helper->assign([5, 8, 3]);
        self::assertEquals(5, (string) $this->helper->next());
        self::assertEquals(5, (string) $this->helper->current());
        self::assertEquals(8, (string) $this->helper->next());
        self::assertEquals(5, (string) $this->helper->prev());
    }

    public function testTwoCycles(): void
    {
        $this->helper->assign([5, 8, 3]);
        self::assertEquals(5, (string) $this->helper->next());
        self::assertEquals(2, (string) $this->helper->__invoke([2, 38, 1], 'cycle2')->next());
        self::assertEquals(8, (string) $this->helper->__invoke()->next());
        self::assertEquals(38, (string) $this->helper->setName('cycle2')->next());
    }

    public function testTwoCyclesInLoop(): void
    {
        $expected  = [5, 4, 2, 3];
        $expected2 = [7, 34, 8, 6];
        for ($i = 0; $i < 4; $i++) {
            self::assertEquals($expected[$i], (string) $this->helper->__invoke($expected)->next());
            self::assertEquals($expected2[$i], (string) $this->helper->__invoke($expected2, 'cycle2')->next());
        }
    }

    public function testResetStateClearsSetVariables(): void
    {
        $this->helper->__invoke(['a', 'b', 'c']);
        $this->helper->resetState();

        self::assertSame('', (string) $this->helper);
        self::assertSame(0, $this->helper->key());
        self::assertSame('', (string) $this->helper->next());
        self::assertSame(0, $this->helper->key());
    }

    public function testPointerStartsAtZeroForCustomNamedCycles(): void
    {
        $this->helper->setName('foo');
        self::assertSame(0, $this->helper->key());
    }
}
