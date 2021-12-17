<?php

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\Cycle.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class CycleTest extends TestCase
{
    /**
     * @var Helper\Cycle
     */
    public $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->helper = new Helper\Cycle();
    }

    public function testCycleMethodReturnsObjectInstance(): void
    {
        $cycle = $this->helper->__invoke();
        $this->assertInstanceOf(Helper\Cycle::class, $cycle);
    }

    public function testAssignAndGetValues(): void
    {
        $this->helper->assign(['a', 1, 'asd']);
        $this->assertEquals(['a', 1, 'asd'], $this->helper->getAll());
    }

    public function testCycleMethod(): void
    {
        $this->helper->__invoke(['a', 1, 'asd']);
        $this->assertEquals(['a', 1, 'asd'], $this->helper->getAll());
    }

    public function testToString(): void
    {
        $this->helper->__invoke(['a', 1, 'asd']);
        $this->assertEquals('a', (string) $this->helper->toString());
    }

    public function testNextValue(): void
    {
        $this->helper->assign(['a', 1, 3]);
        $this->assertEquals('a', (string) $this->helper->next());
        $this->assertEquals(1, (string) $this->helper->next());
        $this->assertEquals(3, (string) $this->helper->next());
        $this->assertEquals('a', (string) $this->helper->next());
        $this->assertEquals(1, (string) $this->helper->next());
    }

    public function testPrevValue(): void
    {
        $this->helper->assign([4, 1, 3]);
        $this->assertEquals(3, (string) $this->helper->prev());
        $this->assertEquals(1, (string) $this->helper->prev());
        $this->assertEquals(4, (string) $this->helper->prev());
        $this->assertEquals(3, (string) $this->helper->prev());
        $this->assertEquals(1, (string) $this->helper->prev());
    }

    public function testRewind(): void
    {
        $this->helper->assign([5, 8, 3]);
        $this->assertEquals(5, (string) $this->helper->next());
        $this->assertEquals(8, (string) $this->helper->next());
        $this->helper->rewind();
        $this->assertEquals(5, (string) $this->helper->next());
        $this->assertEquals(8, (string) $this->helper->next());
    }

    public function testMixedMethods(): void
    {
        $this->helper->assign([5, 8, 3]);
        $this->assertEquals(5, (string) $this->helper->next());
        $this->assertEquals(5, (string) $this->helper->current());
        $this->assertEquals(8, (string) $this->helper->next());
        $this->assertEquals(5, (string) $this->helper->prev());
    }

    public function testTwoCycles(): void
    {
        $this->helper->assign([5, 8, 3]);
        $this->assertEquals(5, (string) $this->helper->next());
        $this->assertEquals(2, (string) $this->helper->__invoke([2, 38, 1], 'cycle2')->next());
        $this->assertEquals(8, (string) $this->helper->__invoke()->next());
        $this->assertEquals(38, (string) $this->helper->setName('cycle2')->next());
    }

    public function testTwoCyclesInLoop(): void
    {
        $expected = [5,4,2,3];
        $expected2 = [7,34,8,6];
        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals($expected[$i], (string) $this->helper->__invoke($expected)->next());
            $this->assertEquals($expected2[$i], (string) $this->helper->__invoke($expected2, 'cycle2')->next());
        }
    }
}
