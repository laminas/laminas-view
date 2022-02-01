<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception;
use Laminas\View\Helper;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

use function array_walk_recursive;

use const PHP_EOL;

class HtmlListTest extends TestCase
{
    /** @var Helper\HtmlList */
    public $helper;
    private View $view;

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

    public function testMakeUnorderedList(): void
    {
        $items = ['one', 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);
        foreach ($items as $item) {
            $this->assertStringContainsString('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeOrderedList(): void
    {
        $items = ['one', 'two', 'three'];

        $list = $this->helper->__invoke($items, true);

        $this->assertStringContainsString('<ol>', $list);
        $this->assertStringContainsString('</ol>', $list);
        foreach ($items as $item) {
            $this->assertStringContainsString('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeUnorderedListWithAttribs(): void
    {
        $items   = ['one', 'two', 'three'];
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

    public function testMakeOrderedListWithAttribs(): void
    {
        $items   = ['one', 'two', 'three'];
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

    public function testMakeNestedUnorderedList(): void
    {
        $items = ['one', ['four', 'five', 'six'], 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('</ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('one<ul>' . PHP_EOL . '<li>four', $list);
        $this->assertStringContainsString('<li>six</li>' . PHP_EOL . '</ul>'
            . PHP_EOL . '</li>' . PHP_EOL . '<li>two', $list);
    }

    public function testMakeNestedDeepUnorderedList(): void
    {
        $items = ['one', ['four', ['six', 'seven', 'eight'], 'five'], 'two', 'three'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('</ul>' . PHP_EOL, $list);
        $this->assertStringContainsString('one<ul>' . PHP_EOL . '<li>four', $list);
        $this->assertStringContainsString('<li>four<ul>' . PHP_EOL . '<li>six', $list);
        $this->assertStringContainsString('<li>five</li>' . PHP_EOL . '</ul>'
            . PHP_EOL . '</li>' . PHP_EOL . '<li>two', $list);
    }

    public function testListWithValuesToEscapeForLaminas2283(): void
    {
        $items = ['one <small> test', 'second & third', 'And \'some\' "final" test'];

        $list = $this->helper->__invoke($items);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);

        $this->assertStringContainsString('<li>one &lt;small&gt; test</li>', $list);
        $this->assertStringContainsString('<li>second &amp; third</li>', $list);
        $this->assertStringContainsString('<li>And &#039;some&#039; &quot;final&quot; test</li>', $list);
    }

    public function testListEscapeSwitchedOffForLaminas2283(): void
    {
        $items = ['one <b>small</b> test'];

        $list = $this->helper->__invoke($items, false, false, false);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);

        $this->assertStringContainsString('<li>one <b>small</b> test</li>', $list);
    }

    public function testEscapeFlagHonoredForMultidimensionalLists(): void
    {
        $items = ['<b>one</b>', ['<b>four</b>', '<b>five</b>', '<b>six</b>'], '<b>two</b>', '<b>three</b>'];

        $list = $this->helper->__invoke($items, false, false, false);

        foreach ($items[1] as $item) {
            $this->assertStringContainsString($item, $list);
        }
    }

    /**
     * Added the s modifier to match newlines after Laminas-5018
     */
    public function testAttribsPassedIntoMultidimensionalLists(): void
    {
        $items = ['one', ['four', 'five', 'six'], 'two', 'three'];

        $list = $this->helper->__invoke($items, false, ['class' => 'foo']);

        foreach ($items[1] as $item) {
            $this->assertMatchesRegularExpression('#<ul[^>]*?class="foo"[^>]*>.*?(<li>' . $item . ')#s', $list);
        }
    }

    public function testEscapeFlagShouldBePassedRecursively(): void
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

        $list = $this->helper->__invoke($items, false, [], false);

        $this->assertStringContainsString('<ul>', $list);
        $this->assertStringContainsString('</ul>', $list);

        array_walk_recursive($items, [$this, 'validateItems'], $list);
    }

    public function validateItems(string $value, int $key, string $userdata): void
    {
        $this->assertStringContainsString('<li>' . $value, $userdata);
    }

    public function testEmptyItems(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->helper->__invoke([]);
    }

    public function testThatListAttributesHaveTheExpectedValue(): void
    {
        $result = ($this->helper)(['foo'], false, ['class' => 'jim', 'data-foo' => null, 'data-bar' => '&']);
        $expect = '<ul class="jim" data-foo="" data-bar="&amp;">';
        self::assertStringContainsString($expect, $result);
    }
}
