<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use Laminas\View\Renderer\PhpRenderer as View;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class HtmlListTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->view   = new View();
        $this->helper = new Helper\HtmlList();
        $this->helper->setView($this->view);
    }

    public function tearDown()
    {
        unset($this->helper);
    }

    public function testMakeUnorderedList()
    {
        $items = ['one', 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeOrderedList()
    {
        $items = ['one', 'two', 'three'];

        $list = $this->helper->__invoke($items, true);

        $this->assertContains('<ol>', $list);
        $this->assertContains('</ol>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeUnorderedListWithAttribs()
    {
        $items = ['one', 'two', 'three'];
        $attribs = ['class' => 'selected', 'name' => 'list'];

        $list = $this->helper->__invoke($items, false, $attribs);

        $this->assertContains('<ul', $list);
        $this->assertContains('class="selected"', $list);
        $this->assertContains('name="list"', $list);
        $this->assertContains('</ul>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeOrderedListWithAttribs()
    {
        $items = ['one', 'two', 'three'];
        $attribs = ['class' => 'selected', 'name' => 'list'];

        $list = $this->helper->__invoke($items, true, $attribs);

        $this->assertContains('<ol', $list);
        $this->assertContains('class="selected"', $list);
        $this->assertContains('name="list"', $list);
        $this->assertContains('</ol>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    /*
     * @group Laminas-5018
     */
    public function testMakeNestedUnorderedList()
    {
        $items = ['one', ['four', 'five', 'six'], 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertContains('<ul>' . PHP_EOL, $list);
        $this->assertContains('</ul>' . PHP_EOL, $list);
        $this->assertContains('one<ul>' . PHP_EOL.'<li>four', $list);
        $this->assertContains('<li>six</li>' . PHP_EOL . '</ul>' .
            PHP_EOL . '</li>' . PHP_EOL . '<li>two', $list);
    }

    /*
     * @group Laminas-5018
     */
    public function testMakeNestedDeepUnorderedList()
    {
        $items = ['one', ['four', ['six', 'seven', 'eight'], 'five'], 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertContains('<ul>' . PHP_EOL, $list);
        $this->assertContains('</ul>' . PHP_EOL, $list);
        $this->assertContains('one<ul>' . PHP_EOL . '<li>four', $list);
        $this->assertContains('<li>four<ul>' . PHP_EOL . '<li>six', $list);
        $this->assertContains('<li>five</li>' . PHP_EOL . '</ul>' .
            PHP_EOL . '</li>' . PHP_EOL . '<li>two', $list);
    }

    public function testListWithValuesToEscapeForLaminas2283()
    {
        $items = ['one <small> test', 'second & third', 'And \'some\' "final" test'];

        $list = $this->helper->__invoke($items);

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);

        $this->assertContains('<li>one &lt;small&gt; test</li>', $list);
        $this->assertContains('<li>second &amp; third</li>', $list);
        $this->assertContains('<li>And &#039;some&#039; &quot;final&quot; test</li>', $list);
    }

    public function testListEscapeSwitchedOffForLaminas2283()
    {
        $items = ['one <b>small</b> test'];

        $list = $this->helper->__invoke($items, false, false, false);

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);

        $this->assertContains('<li>one <b>small</b> test</li>', $list);
    }

    /**
     * @group Laminas-2527
     */
    public function testEscapeFlagHonoredForMultidimensionalLists()
    {
        $items = ['<b>one</b>', ['<b>four</b>', '<b>five</b>', '<b>six</b>'], '<b>two</b>', '<b>three</b>'];

        $list = $this->helper->__invoke($items, false, false, false);

        foreach ($items[1] as $item) {
            $this->assertContains($item, $list);
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
            $this->assertRegexp('#<ul[^>]*?class="foo"[^>]*>.*?(<li>' . $item . ')#s', $list);
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

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);

        array_walk_recursive($items, [$this, 'validateItems'], $list);
    }

    public function validateItems($value, $key, $userdata)
    {
        $this->assertContains('<li>' . $value, $userdata);
    }

    /**
     * @group Laminas-6063
     */
    public function testEmptyItems()
    {
        $this->setExpectedException('Laminas\View\Exception\InvalidArgumentException');
        $this->helper->__invoke([]);
    }
}
