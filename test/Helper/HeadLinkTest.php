<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\Escaper\EscaperInterface;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadLink;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type DoctypeID from Doctype */
final class HeadLinkTest extends TestCase
{
    private HeadLink $helper;
    private EscaperInterface $escaper;

    protected function setUp(): void
    {
        $this->escaper = new Escaper();
        $this->helper  = new HeadLink(
            $this->escaper,
            new Doctype(),
        );
    }

    /** @param DoctypeID $doctype */
    private function setDoctype(string $doctype): void
    {
        $this->helper = new HeadLink(
            $this->escaper,
            new Doctype($doctype),
        );
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame(
            $this->helper,
            $this->helper->__invoke(),
        );
    }

    public function testStringValueIsEmptyByDefault(): void
    {
        self::assertSame('', $this->helper->__toString());
    }

    public function testIndentHasExpectedValueByDefaultAndWhenChanged(): void
    {
        $this->helper->append(['rel' => 'foo']);
        self::assertSame('<link rel="foo">', $this->helper->toString());
        $this->helper->setIndent('!!');
        self::assertSame('!!<link rel="foo">', $this->helper->toString());
        self::assertSame('bob<link rel="foo">', $this->helper->toString('bob'));
    }

    public function testStylesheetsHaveTheExpectedDefaultAttributeValues(): void
    {
        $this->helper->appendStylesheet('foo.css');
        self::assertSame(
            '<link rel="stylesheet" href="foo.css" type="text&#x2F;css">',
            $this->helper->toString(),
        );
    }

    public function testStylesheetsCanHaveArbitraryAttributes(): void
    {
        $this->helper->appendStylesheet('foo.css', ['data-baz' => 'bing']);
        self::assertSame(
            '<link data-baz="bing" rel="stylesheet" href="foo.css" type="text&#x2F;css">',
            $this->helper->toString(),
        );
    }

    public function testStylesheetsHaveTheExpectedOrder(): void
    {
        $this->helper->appendStylesheet('a.css');
        $this->helper->prependStylesheet('b.css');
        $this->helper->appendStylesheet('c.css');

        $expect = <<<'HTML'
            <link rel="stylesheet" href="b.css" type="text&#x2F;css">
            <link rel="stylesheet" href="a.css" type="text&#x2F;css">
            <link rel="stylesheet" href="c.css" type="text&#x2F;css">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testSetStylesheetEmptiesTheList(): void
    {
        $this->helper->appendStylesheet('a.css');
        $this->helper->prependStylesheet('b.css');
        $this->helper->setStylesheet('c.css');

        $expect = <<<'HTML'
            <link rel="stylesheet" href="c.css" type="text&#x2F;css">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testXhtmlDoctypeYieldsSelfClosingTag(): void
    {
        $this->setDoctype(Doctype::XHTML11);
        $this->helper->append(['rel' => 'preload', 'as' => 'font', 'href' => 'a.woff']);

        self::assertSame(
            '<link rel="preload" as="font" href="a.woff" />',
            $this->helper->toString(),
        );
    }

    public function testDoesNotAllowDuplicateStylesheets(): void
    {
        $this->helper->appendStylesheet('foo');
        $this->helper->appendStylesheet('foo');
        self::assertSame(
            '<link rel="stylesheet" href="foo" type="text&#x2F;css">',
            $this->helper->toString(),
        );
    }

    public function testDuplicatesUrisAreNotFilteredWithDifferentRelAttributes(): void
    {
        $this->helper->append(['rel' => 'a', 'href' => 'foo']);
        $this->helper->append(['rel' => 'b', 'href' => 'foo']);

        $expect = <<<'HTML'
            <link rel="a" href="foo">
            <link rel="b" href="foo">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testTheSeparatorCanBeChangedAtRuntime(): void
    {
        $this->helper->append(['rel' => 'a', 'href' => 'foo']);
        $this->helper->append(['rel' => 'b', 'href' => 'foo']);
        $this->helper->setSeparator('Kermit');

        $expect = <<<'HTML'
            <link rel="a" href="foo">Kermit<link rel="b" href="foo">
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testResetStateRevertsAllPropertiesToConfiguredDefaults(): void
    {
        $this->helper->prepend(['rel' => 'a']);
        $this->helper->prepend(['rel' => 'b']);
        $this->helper->setSeparator('-')
            ->setIndent('!');

        $expect = <<<'HTML'
            !<link rel="b">-!<link rel="a">
            HTML;

        self::assertSame($expect, $this->helper->toString());

        $this->helper->resetState();

        self::assertSame('', $this->helper->toString());

        $this->helper->prepend(['rel' => 'a']);
        $this->helper->prepend(['rel' => 'b']);

        $expect = <<<'HTML'
            <link rel="b">
            <link rel="a">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testAddingItemsWithNoAttributesIsANoOp(): void
    {
        $this->helper->append([]);
        $this->helper->append([]);
        $this->helper->append([]);

        self::assertSame('', $this->helper->toString());
    }

    public function testOrderOfArbitraryItems(): void
    {
        $this->helper->append(['href' => 'a']);
        $this->helper->prepend(['href' => 'b']);
        $this->helper->append(['href' => 'c']);

        $expect = <<<'HTML'
            <link href="b">
            <link href="a">
            <link href="c">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testSetItemClearsTheList(): void
    {
        $this->helper->append(['href' => 'a']);
        $this->helper->prepend(['href' => 'b']);
        $this->helper->set(['href' => 'c']);

        $expect = <<<'HTML'
            <link href="c">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testArgumentsToInvokeAppendTheItem(): void
    {
        $this->helper->__invoke(['rel' => 'next']);
        $expect = <<<'HTML'
            <link rel="next">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }
}
