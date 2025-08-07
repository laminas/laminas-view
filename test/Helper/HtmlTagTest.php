<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HtmlTag;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type DoctypeID from Doctype */
final class HtmlTagTest extends TestCase
{
    private HtmlTag $helper;

    protected function setUp(): void
    {
        $this->helper = new HtmlTag(
            new Escaper(),
            new Doctype(),
        );
    }

    /** @param DoctypeID $doctype */
    private function setDoctype(string $doctype): void
    {
        $this->helper = new HtmlTag(
            new Escaper(),
            new Doctype($doctype),
        );
    }

    public function testBareHtmlTagByDefault(): void
    {
        self::assertSame('<html>', $this->helper->openTag());
        self::assertSame('</html>', $this->helper->closeTag());
    }

    public function testArbitraryAttributeViaInvoke(): void
    {
        $this->helper->__invoke(['foo' => 'bar']);

        self::assertStringContainsString('foo="bar"', $this->helper->openTag());
    }

    public function testArbitraryAttributesViaSetter(): void
    {
        $this->helper->setAttributes(['foo' => 'bar']);

        self::assertStringContainsString('foo="bar"', $this->helper->openTag());
    }

    public function testAddingSingleAttributeViaSetter(): void
    {
        $this->helper->setAttribute('baz', 'bat');
        self::assertStringContainsString('baz="bat"', $this->helper->openTag());
    }

    public function testSetAttributesDestroysExisting(): void
    {
        $this->helper->setAttributes(['bing' => 'bong']);
        $this->helper->setAttributes(['thing' => 'thang']);

        self::assertStringContainsString('thing="thang"', $this->helper->openTag());
        self::assertStringNotContainsString('bing="bong"', $this->helper->openTag());
    }

    public function testAttributesAreEscaped(): void
    {
        self::assertStringContainsString(
            'foo="a&#x20;b"',
            $this->helper->setAttribute('foo', 'a b')->openTag(),
        );
    }

    public function testNamespaceIsAddedToXhtmlDoc(): void
    {
        $this->setDoctype(Doctype::XHTML1_STRICT);
        $this->helper->addXhtmlNamespace(true);
        self::assertStringContainsString('xmlns="https', $this->helper->openTag());
    }

    public function testNamespaceIsNotAddedToXhtmlDocWhenNotExplicitlyActivated(): void
    {
        $this->setDoctype(Doctype::XHTML1_STRICT);
        self::assertStringNotContainsString('xmlns="https', $this->helper->openTag());
    }

    public function testStateReset(): void
    {
        $this->setDoctype(Doctype::XHTML1_STRICT);
        $this->helper->setAttributes(['foo' => 'bar'])
            ->addXhtmlNamespace(true)
            ->resetState();

        self::assertSame('<html>', $this->helper->openTag());
    }
}
