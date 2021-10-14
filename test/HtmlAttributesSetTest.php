<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\Escaper\Escaper;
use Laminas\View\HtmlAttributesSet;
use PHPUnit\Framework\TestCase;

class HtmlAttributesSetTest extends TestCase
{
    /** @var HtmlAttributesSet */
    private $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new HtmlAttributesSet(new Escaper());
    }

    public function testThatTheSetIsInitiallyEmpty(): void
    {
        self::assertCount(0, $this->helper);
    }

    public function testThatAnEmptySetYieldsAnEmptyString(): void
    {
        self::assertEquals('', (string) $this->helper);
    }

    public function testThatSetMutatesTheExistingAttributes(): void
    {
        $helper = new HtmlAttributesSet(new Escaper(), ['foo' => 'bar']);
        self::assertCount(1, $helper);
        self::assertStringContainsString('foo="bar"', (string) $helper);

        $helper->set(['mushrooms' => 'nice', 'foo' => 'goats']);

        self::assertCount(2, $helper);
        self::assertStringContainsString('foo="goats" mushrooms="nice"', (string) $helper);
    }

    public function testThatAClassListWillBeImploded(): void
    {
        $this->helper->set(['class' => ['foo', 'bar']]);
        self::assertStringContainsString('class="foo&#x20;bar"', (string) $this->helper);
    }

    public function testThatItemsCanBeAddedToAClassList(): void
    {
        $this->helper->set(['class' => 'foo']);
        $this->helper->add('class', 'bar');
        self::assertStringContainsString('class="foo&#x20;bar"', (string) $this->helper);
    }

    public function testThatMergingAnArrayIsPossible(): void
    {
        $a = new HtmlAttributesSet(new Escaper(), ['foo' => 'foo']);
        $a->merge(['bar' => 'bar']);

        self::assertStringContainsString('foo="foo" bar="bar"', (string) $a);
    }

    public function testThatMergingAClassListYieldsExpectedValues(): void
    {
        $a = new HtmlAttributesSet(new Escaper(), ['foo' => 'foo']);
        $a->merge(['foo' => 'bar']);

        self::assertStringContainsString('foo="foo&#x20;bar"', (string) $a);
    }

    public function testHasValueForScalars(): void
    {
        self::assertFalse($this->helper->hasValue('nuts', 'pea'));
        $this->helper->add('nuts', 'pea');
        self::assertTrue($this->helper->hasValue('nuts', 'pea'));
    }

    public function testHasValueForArrays(): void
    {
        $this->helper->set(['nuts' => 'walnut']);
        self::assertFalse($this->helper->hasValue('nuts', 'pea'));
        self::assertTrue($this->helper->hasValue('nuts', 'walnut'));
        $this->helper->add('nuts', 'pea');
        self::assertTrue($this->helper->hasValue('nuts', 'pea'));
        self::assertTrue($this->helper->hasValue('nuts', 'walnut'));
    }
}
