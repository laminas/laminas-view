<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadMeta;
use PHPUnit\Framework\TestCase;

use function substr_count;

use const PHP_EOL;

/** @psalm-import-type DoctypeID from Doctype */
final class HeadMetaTest extends TestCase
{
    private HeadMeta $helper;
    private Escaper $escaper;

    protected function setUp(): void
    {
        $this->escaper = new Escaper();
        $this->helper  = new HeadMeta(
            new Doctype(),
            $this->escaper,
        );
    }

    /** @param DoctypeID $doctype */
    private function setDoctype(string $doctype): void
    {
        $doctype = new Doctype($doctype);

        $this->helper = new HeadMeta(
            $doctype,
            $this->escaper,
        );
    }

    public function testHeadMetaReturnsObjectInstance(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testBasicOperationOfNamedMeta(): void
    {
        $this->helper->appendName('description', 'some description')
            ->prependName('keywords', 'foo, bar')
            ->setName('generator', 'laminas');

        $expect = <<<'HTML'
            <meta content="foo,&#x20;bar" name="keywords">
            <meta content="some&#x20;description" name="description">
            <meta content="laminas" name="generator">
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testBasicOperationOfHttpEquiv(): void
    {
        $this->helper->appendHttpEquiv('Content-type', 'x-application/muppets')
            ->setHttpEquiv('muppet', 'Miss Piggy')
            ->prependHttpEquiv('refresh', '5; url=Kermit');

        $expect = <<<'HTML'
            <meta content="5&#x3B;&#x20;url&#x3D;Kermit" http-equiv="refresh">
            <meta content="x-application&#x2F;muppets" http-equiv="Content-type">
            <meta content="Miss&#x20;Piggy" http-equiv="muppet">
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testBasicOperationOfItemprop(): void
    {
        $this->helper->appendItemprop('name', 'Fred')
            ->setItemprop('taxId', '123456')
            ->prependItemprop('telephone', '+441234567890');

        $expect = <<<'HTML'
            <meta content="&#x2B;441234567890" itemprop="telephone">
            <meta content="Fred" itemprop="name">
            <meta content="123456" itemprop="taxId">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testBasicOperationOfProperty(): void
    {
        $this->helper->appendProperty('og:title', 'Some Title')
            ->setProperty('og:description', 'Some description')
            ->prependProperty('og:image', 'https://example.com/foo.jpg');

        $expect = <<<'HTML'
            <meta content="https&#x3A;&#x2F;&#x2F;example.com&#x2F;foo.jpg" property="og&#x3A;image">
            <meta content="Some&#x20;Title" property="og&#x3A;title">
            <meta content="Some&#x20;description" property="og&#x3A;description">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testIndentationIsHonored(): void
    {
        $this->helper->setIndent(4);
        $this->helper->appendName('keywords', 'foo bar');
        $this->helper->appendName('seo', 'baz bat');
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <meta content=');
        $this->assertEquals(2, $scripts);
    }

    public function testIndentationCanBeAString(): void
    {
        $this->helper->setIndent("\t\t");
        $this->helper->appendName('keywords', 'foo bar');
        $this->helper->appendName('seo', 'baz bat');
        $string = $this->helper->toString();

        $scripts = substr_count($string, "\t\t" . '<meta content=');
        $this->assertEquals(2, $scripts);
    }

    public function testSelfClosingTagForXmlBasedDoctypes(): void
    {
        $this->setDoctype(Doctype::XHTML1_STRICT);
        $this->helper->appendName('bar', 'foo');
        $expect = <<<'HTML'
            <meta content="foo" name="bar" />
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testCharsetIsPrependedWhenSet(): void
    {
        $this->setDoctype(Doctype::HTML5);
        $this->helper->setName('description', 'foo');
        $this->helper->setCharset('utf-8');
        $expect = <<<'HTML'
            <meta charset="utf-8">
            <meta content="foo" name="description">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testSetCharsetWillClearExistingCharset(): void
    {
        $this->setDoctype(Doctype::HTML5);
        $this->helper->append(['charset' => 'foo']);
        $this->helper->prepend(['charset' => 'bar']);
        $this->helper->setCharset('utf-8');
        $expect = <<<'HTML'
            <meta charset="utf-8">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testMultipleCharsetIsPossibleUsingPrependAndAppend(): void
    {
        $this->helper->append(['charset' => 'foo']);
        $this->helper->prepend(['charset' => 'bar']);
        $expect = <<<'HTML'
            <meta charset="bar">
            <meta charset="foo">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testEmptyAttributeArraysAreIgnored(): void
    {
        $this->helper->__invoke(null, null, [])
            ->append([])
            ->prepend([]);

        self::assertSame('', $this->helper->toString());
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testInvokeWillAppendNameWithNonNullArguments(): void
    {
        $this->helper->__invoke('foo', 'bar', ['baz' => 'bat']);
        $expect = <<<'HTML'
            <meta baz="bat" content="bar" name="foo">
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testInvokeWillAppendAttributesWhenNameAndContentAreNull(): void
    {
        $this->helper->__invoke(null, null, ['baz' => 'bat']);
        $expect = <<<'HTML'
            <meta baz="bat">
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testIdenticalItemsAreIgnoredWhenAppending(): void
    {
        $this->helper->appendName('foo', 'bar');
        $this->helper->appendName('foo', 'bar');
        $expect = <<<'HTML'
            <meta content="bar" name="foo">
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testIdenticalItemsAreIgnoredWhenPrepending(): void
    {
        $this->helper->prependName('foo', 'bar');
        $this->helper->prependName('foo', 'bar');
        $expect = <<<'HTML'
            <meta content="bar" name="foo">
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testSeparatorCanBeChangedAndWillBeEscaped(): void
    {
        $this->helper->prependName('foo', 'bar')
            ->prependName('bar', 'baz')
            ->setSeparator(PHP_EOL . '&');
        $expect = <<<'HTML'
            <meta content="baz" name="bar">
            &amp;<meta content="bar" name="foo">
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testIndentWillBeEscaped(): void
    {
        $this->helper->prependName('foo', 'bar')
            ->prependName('bar', 'baz')
            ->setIndent('&');
        $expect = <<<'HTML'
            &amp;<meta content="baz" name="bar">
            &amp;<meta content="bar" name="foo">
            HTML;
        self::assertSame($expect, $this->helper->toString());
    }

    public function testAllMetaMutationMethodsReturnSelf(): void
    {
        self::assertSame($this->helper, $this->helper->appendName('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->prependName('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->setName('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->appendProperty('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->prependProperty('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->setProperty('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->appendItemprop('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->prependItemprop('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->setItemprop('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->appendHttpEquiv('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->prependHttpEquiv('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->setHttpEquiv('foo', 'bar'));
        self::assertSame($this->helper, $this->helper->setCharset('foo'));
        self::assertSame($this->helper, $this->helper->append(['foo' => 'bar']));
        self::assertSame($this->helper, $this->helper->prepend(['foo' => 'bar']));
    }

    public function testResetStateClearsAllMutableProperties(): void
    {
        $this->helper->setSeparator('-')
            ->setIndent(2)
            ->append(['foo' => 'bar'])
            ->append(['baz' => 'bat']);

        $expect = <<<'HTML'
              <meta foo="bar">-  <meta baz="bat">
            HTML;

        self::assertSame($expect, $this->helper->toString());

        $this->helper->resetState();

        self::assertSame('', $this->helper->toString());

        $this->helper->append(['foo' => 'bar'])
            ->append(['baz' => 'bat']);

        $expect = <<<'HTML'
            <meta foo="bar">
            <meta baz="bat">
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }
}
