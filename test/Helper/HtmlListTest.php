<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Exception;
use Laminas\View\Helper;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HtmlListTest extends TestCase
{
    /**
     * @var Helper\HtmlList
     */
    public $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        $this->view   = new View();
        $this->helper = new Helper\HtmlList();
        $this->helper->setView($this->view);
    }

    protected function tearDown(): void
    {
        unset($this->helper);
    }

    public function testMakeUnorderedList()
    {
        $items = ['one', 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);
        foreach ($items as $item) {
            $this->assertStringContainsString('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeOrderedList()
    {
        $items = ['one', 'two', 'three'];

        $list = $this->helper->__invoke($items, true);

        $this->assertStringContainsString('<ol>', $list);
        $this->assertStringContainsString('</ol>', $list);
        foreach ($items as $item) {
            $this->assertStringContainsString('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeUnorderedListWithAttribs()
    {
        $items = ['one', 'two', 'three'];
        $attribs = ['class' => 'selected', 'name' => 'list'];

        $list = $this->helper->__invoke($items, false, $attribs);

        $this->assertStringContainsString('<ul', $list);
        $this->assertStringContainsString('class="selected"', $list);
        $this->assertStringContainsString('name="list"', $list);
        $this->assertStringContainsString('</ul>', $list);
        foreach ($items as $item) {
            $this->assertStringContainsString('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeOrderedListWithAttribs()
    {
        $items = ['one', 'two', 'three'];
        $attribs = ['class' => 'selected', 'name' => 'list'];

        $list = $this->helper->__invoke($items, true, $attribs);

        $this->assertStringContainsString('<ol', $list);
        $this->assertStringContainsString('class="selected"', $list);
        $this->assertStringContainsString('name="list"', $list);
        $this->assertStringContainsString('</ol>', $list);
        foreach ($items as $item) {
            $this->assertStringContainsString('<li>' . $item . '</li>', $list);
        }
    }

    /*
     * @group Laminas-5018
     */
    public function testMakeNestedUnorderedList()
    {
        $items = ['one', ['four', 'five', 'six'], 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('</ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('one<ul>' . PHP_EOL.'<li>four', $list);
        $this->assertStringContainsString('<li>six</li>' . PHP_EOL . '</ul>' .
            PHP_EOL . '</li>' . PHP_EOL . '<li>two', $list);
    }

    /*
     * @group Laminas-5018
     */
    public function testMakeNestedDeepUnorderedList()
    {
        $items = ['one', ['four', ['six', 'seven', 'eight'], 'five'], 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('</ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('one<ul>' . PHP_EOL . '<li>four', $list);
        $this->assertStringContainsString('<li>four<ul>' . PHP_EOL . '<li>six', $list);
        $this->assertStringContainsString('<li>five</li>' . PHP_EOL . '</ul>' .
            PHP_EOL . '</li>' . PHP_EOL . '<li>two', $list);
    }

    public function testListWithValuesToEscapeForLaminas2283()
    {
        $items = ['one <small> test', 'second & third', 'And \'some\' "final" test'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);

        $this->assertStringContainsString('<li>one &lt;small&gt; test</li>', $list);
        $this->assertStringContainsString('<li>second &amp; third</li>', $list);
        $this->assertStringContainsString('<li>And &#039;some&#039; &quot;final&quot; test</li>', $list);
    }

    public function testListEscapeSwitchedOffForLaminas2283()
    {
        $items = ['one <b>small</b> test'];

        $list = $this->helper->__invoke($items, false, false, false);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);

        $this->assertStringContainsString('<li>one <b>small</b> test</li>', $list);
    }

    /**
     * @group Laminas-2527
     */
    public function testEscapeFlagHonoredForMultidimensionalLists()
    {
        $items = ['<b>one</b>', ['<b>four</b>', '<b>five</b>', '<b>six</b>'], '<b>two</b>', '<b>three</b>'];

        $list = $this->helper->__invoke($items, false, false, false);

        foreach ($items[1] as $item) {
            $this->assertStringContainsString($item, $list);
        }
    }

    /**
     * @group Laminas-2527
     * Added the s modifier to match newlines after Laminas-5018
     */
    public function testAttribsPassedIntoMultidimensionalLists()
    {
        $items = ['one', ['four', 'five', 'six'], 'two', 'three'];

        $list = $this->helper->__invoke($items, false, ['class' => 'foo']);

        foreach ($items[1] as $item) {
            $this->assertMatchesRegularExpression('#<ul[^>]*?class="foo"[^>]*>.*?(<li>' . $item . ')#s', $list);
        }
    }

    /**
     * @group Laminas-2870
     */
    public function testEscapeFlagShouldBePassedRecursively()
    {
        $items = [
            '<b>one</b>',
            [
                '<b>four</b>',
                '<b>five</b>',
                '<b>six</b>',
                [
                    '<b>two</b>',
                    '<b>three</b>',
                ],
            ],
        ];

        $list = $this->helper->__invoke($items, false, false, false);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);

        array_walk_recursive($items, [$this, 'validateItems'], $list);
    }

    public function validateItems($value, $key, $userdata)
    {
        $this->assertStringContainsString('<li>' . $value, $userdata);
    }

    /**
     * @group Laminas-6063
     */
    public function testEmptyItems()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->helper->__invoke([]);
    }
}
