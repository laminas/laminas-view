<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadStyle;
use Laminas\View\Helper\Placeholder\Position;
use PHPUnit\Framework\TestCase;

final class HeadStyleTest extends TestCase
{
    private HeadStyle $helper;

    protected function setUp(): void
    {
        $this->helper = new HeadStyle(
            new Escaper(),
            new Doctype(),
        );
    }

    private function setDoctypeToXhtml(): void
    {
        $this->helper = new HeadStyle(
            new Escaper(),
            new Doctype(Doctype::XHTML1_STRICT),
        );
    }

    public function testInvokeWithoutArgumentsReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testAppendStyleAppendsStyleToStack(): void
    {
        $this->helper->appendStyle('* { display: none; }', ['media' => 'screen']);
        $this->helper->appendStyle('* { display: block; }', ['media' => 'print']);

        $expect = <<<'HTML'
            <style media="screen">
            * { display: none; }
            </style>
            <style media="print">
            * { display: block; }
            </style>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testPrependStylePrependsStyleToStack(): void
    {
        $this->helper->prependStyle('* { display: none; }', ['media' => 'screen']);
        $this->helper->prependStyle('* { display: block; }', ['media' => 'print']);

        $expect = <<<'HTML'
            <style media="print">
            * { display: block; }
            </style>
            <style media="screen">
            * { display: none; }
            </style>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testSetOverwritesStack(): void
    {
        $this->helper->setStyle('* { display: none; }', ['media' => 'screen']);
        $this->helper->setStyle('* { display: block; }', ['media' => 'print']);

        $expect = <<<'HTML'
            <style media="print">
            * { display: block; }
            </style>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testAttributesAreEmitted(): void
    {
        $this->helper->setStyle('a {}', [
            'fizz' => 'buzz',
            'bing' => 'bong',
        ]);

        $expect = <<<'HTML'
            <style bing="bong" fizz="buzz">
            a {}
            </style>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testTheTypeAttributeIsAddedForNonHtml5Doctypes(): void
    {
        $this->setDoctypeToXhtml();

        $this->helper->appendStyle('a {}');

        $expect = <<<'HTML'
            <style type="text&#x2F;css">
            a {}
            </style>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testMediaAttributeCanHaveSpaceInCommaSeparatedString(): void
    {
        $this->helper->appendStyle('a { }', ['media' => 'screen, projection']);
        $string = $this->helper->toString();
        self::assertStringContainsString('media="screen,&#x20;projection"', $string);
    }

    public function testMediaAttributeCanContainARegularMediaQuery(): void
    {
        $this->helper->appendStyle('a { }', ['media' => 'screen and (max-width: 100px)']);
        $string = $this->helper->toString();
        self::assertStringContainsString(
            'media="screen&#x20;and&#x20;&#x28;max-width&#x3A;&#x20;100px&#x29;"',
            $string,
        );
    }

    public function testHeadStyleProxiesProperly(): void
    {
        $style1 = 'a {}';
        $style2 = 'h1 {}';
        $style3 = 'h2 {}';

        $this->helper->__invoke($style1, [], Position::Set)
                     ->__invoke($style2, [], Position::Append)
                     ->__invoke($style3, [], Position::Prepend);

        $expect = <<<'HTML'
            <style>
            h2 {}
            </style>
            <style>
            a {}
            </style>
            <style>
            h1 {}
            </style>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testCapturing(): void
    {
        $this->helper->captureStart();
        echo '* { display: none; }';
        $this->helper->captureEnd();

        $expect = <<<'HTML'
            <style>
            * { display: none; }
            </style>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testThatEmptyStylesWillYieldAnEmptyValue(): void
    {
        $this->helper->appendStyle('', ['media' => 'screen']);
        self::assertSame('', $this->helper->toString());
    }

    public function testIndentationIsHonored(): void
    {
        $this->helper->setIndent(4);
        $this->helper->appendStyle('a { display: none; }');
        $this->helper->appendStyle('h1 { font-weight: bold }');

        $expect = <<<HTML
                <style>
                a { display: none; }
                </style>
                <style>
                h1 { font-weight: bold }
                </style>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testSerialCapturingWorks(): void
    {
        $this->helper->__invoke()->captureStart();
        echo 'first capture';
        $this->helper->__invoke()->captureEnd();

        $this->helper->__invoke()->captureStart();
        echo 'second capture';
        $this->helper->__invoke()->captureEnd();

        self::assertStringContainsString('first capture', (string) $this->helper);
        self::assertStringContainsString('second capture', (string) $this->helper);
    }

    public function testCaptureWithPrepend(): void
    {
        $this->helper->appendStyle('* { display: none; }');
        $this->helper->captureStart(Position::Prepend, ['muppet' => 'kermit']);
        echo '* { display: flex; }';
        $this->helper->captureEnd();

        $expect = <<<'HTML'
            <style muppet="kermit">
            * { display: flex; }
            </style>
            <style>
            * { display: none; }
            </style>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testCaptureWithSet(): void
    {
        $this->helper->appendStyle('* { display: none; }');
        $this->helper->captureStart(Position::Set, ['muppet' => 'kermit']);
        echo '* { display: flex; }';
        $this->helper->captureEnd();

        $expect = <<<'HTML'
            <style muppet="kermit">
            * { display: flex; }
            </style>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testSeparatorCanBeOdd(): void
    {
        $this->helper->appendStyle('* { display: none; }')
            ->appendStyle('* { display: none; }')
            ->setSeparator('foo')
            ->setIndent('!!');

        $expect = <<<'HTML'
            !!<style>
            !!* { display: none; }
            !!</style>foo<style>
            !!* { display: none; }
            !!</style>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testResetStateClearsAllProperties(): void
    {
        $this->helper->appendStyle('* { display: none; }')
            ->appendStyle('* { display: none; }')
            ->setSeparator('foo')
            ->setIndent('!!')
            ->resetState();

        self::assertSame('', $this->helper->toString());
    }

    public function testResetStateWillDiscardCapture(): void
    {
        $this->helper->captureStart();
        echo 'Foo';
        $this->helper->resetState();

        self::assertSame('', $this->helper->toString());

        try {
            $this->helper->captureStart();
            $this->helper->captureEnd();
        } catch (RuntimeException) {
            self::fail('An exception should not have been thrown');
        }
    }

    public function testEmptyContentIsIgnored(): void
    {
        $this->helper->appendStyle('')
            ->prependStyle('');

        self::assertSame('', $this->helper->toString());

        $this->helper->setStyle('');

        self::assertSame('', $this->helper->toString());
    }

    public function testIndentCanBeAppliedInToString(): void
    {
        $this->helper->appendStyle('foo');

        $expect = <<<'HTML'
            foo<style>
            foofoo
            foo</style>
            HTML;

        self::assertSame($expect, $this->helper->toString('foo'));
    }
}
