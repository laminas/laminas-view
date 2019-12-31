<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \Laminas\View\Helper\AbstractHtmlElement}
 *
 * @covers \Laminas\View\Helper\AbstractHtmlElement
 */
class AbstractHtmlElementTest extends TestCase
{
    /**
     * @var AbstractHtmlElement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->helper = $this->getMockForAbstractClass(AbstractHtmlElement::class);

        $this->helper->setView(new PhpRenderer());
    }

    /**
     * @group 5991
     */
    public function testWillEscapeValueAttributeValuesCorrectly()
    {
        $reflectionMethod = new \ReflectionMethod($this->helper, 'htmlAttribs');

        $reflectionMethod->setAccessible(true);

        $this->assertSame(
            ' data-value="breaking&#x20;your&#x20;HTML&#x20;like&#x20;a&#x20;boss&#x21;&#x20;&#x5C;"',
            $reflectionMethod->invoke($this->helper, ['data-value' => 'breaking your HTML like a boss! \\'])
        );
    }
}
