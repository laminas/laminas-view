<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\HtmlTag;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

use function assert;
use function sprintf;

class HtmlTagTest extends TestCase
{
    /** @var HtmlTag */
    public $helper;
    private View $view;

    protected function setUp(): void
    {
        $this->view   = new View();
        $this->helper = new HtmlTag();
        $this->helper->setView($this->view);
    }

    protected function assertAttribute(string $name, ?string $value = null): void
    {
        $attributes = $this->helper->getAttributes();
        $this->assertArrayHasKey($name, $attributes);
        if ($value) {
            $this->assertEquals($value, $attributes[$name]);
        }
    }

    public function testSettingSingleAttribute(): void
    {
        $this->helper->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $this->assertAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
    }

    public function testAddingMultipleAttributes(): void
    {
        $attribs = [
            'xmlns'  => 'http://www.w3.org/1999/xhtml',
            'prefix' => 'og: http://ogp.me/ns#',
        ];
        $this->helper->setAttributes($attribs);

        foreach ($attribs as $name => $value) {
            $this->assertAttribute($name, $value);
        }
    }

    public function testSettingMultipleAttributesOverwritesExisting(): void
    {
        $this->helper->setAttribute('prefix', 'foobar');

        $attribs = [
            'xmlns'  => 'http://www.w3.org/1999/xhtml',
            'prefix' => 'og: http://ogp.me/ns#',
        ];
        $this->helper->setAttributes($attribs);

        $this->assertCount(2, $this->helper->getAttributes());
        foreach ($attribs as $name => $value) {
            $this->assertAttribute($name, $value);
        }
    }

    public function testRenderingOpenTagWithNoAttributes(): void
    {
        $this->assertEquals('<html>', $this->helper->openTag());
    }

    public function testRenderingOpenTagWithAttributes(): void
    {
        $attribs = [
            'xmlns'    => 'http://www.w3.org/1999/xhtml',
            'xmlns:og' => 'http://ogp.me/ns#',
        ];

        $this->helper->setAttributes($attribs);

        $tag = $this->helper->openTag();

        $this->assertStringStartsWith('<html', $tag);

        $escape = $this->view->plugin('escapehtmlattr');
        assert($escape instanceof EscapeHtmlAttr);

        foreach ($attribs as $name => $value) {
            $this->assertStringContainsString(sprintf('%s="%s"', $name, $escape($value)), $tag);
        }
    }

    public function testRenderingCloseTag(): void
    {
        $this->assertEquals('</html>', $this->helper->closeTag());
    }

    public function testUseNamespacesSetter(): void
    {
        $this->helper->setUseNamespaces(true);
        $this->assertTrue($this->helper->getUseNamespaces());
    }

    public function testAppropriateNamespaceAttributesAreSetIfFlagIsOn(): void
    {
        $doctype = $this->view->plugin('doctype');
        assert($doctype instanceof Doctype);
        $doctype->setDoctype('xhtml');

        $attribs = [
            'prefix' => 'og: http://ogp.me/ns#',
        ];

        $this->helper->setUseNamespaces(true)->setAttributes($attribs);

        $tag = $this->helper->openTag();

        $escape = $this->view->plugin('escapehtmlattr');
        assert($escape instanceof EscapeHtmlAttr);

        $this->assertStringContainsString(sprintf('%s="%s"', 'xmlns', $escape('http://www.w3.org/1999/xhtml')), $tag);
        foreach ($attribs as $name => $value) {
            $this->assertStringContainsString(sprintf('%s="%s"', $name, $escape($value)), $tag);
        }
    }
}
