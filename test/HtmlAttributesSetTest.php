<?php

declare(strict_types=1);

namespace LaminasTest\View;

use ArrayObject;
use Laminas\Escaper\Escaper;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\HtmlAttributesSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type AttributeSet from HtmlAttributesSet */
final class HtmlAttributesSetTest extends TestCase
{
    private HtmlAttributesSet $helper;

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

        self::assertStringContainsString('bar="bar" foo="foo"', (string) $a);
    }

    public function testThatMergingAClassListYieldsExpectedValues(): void
    {
        $a = new HtmlAttributesSet(new Escaper(), ['class' => 'foo']);
        $a->merge(['class' => 'bar']);

        self::assertStringContainsString('class="foo&#x20;bar"', (string) $a);
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

    /**
     * @return list<array{0: AttributeSet, 1: string}>
     */
    public static function eventHandlerProvider(): array
    {
        return [
            [['onclick' => 'doStuff();'], 'onclick="doStuff&#x28;&#x29;&#x3B;"'],
            [['onwhatever' => ['foo' => 'bar']], "onwhatever='&#x7B;&quot;foo&quot;&#x3A;&quot;bar&quot;&#x7D;'"],
        ];
    }

    /** @param AttributeSet $attributes */
    #[DataProvider('eventHandlerProvider')]
    public function testEventHandlers(array $attributes, string $expect): void
    {
        $value = (string) new HtmlAttributesSet(new Escaper(), $attributes);

        self::assertStringContainsString($expect, $value);
    }

    public function testALeadingSpaceIsEmittedWhenAttributesAreNonEmpty(): void
    {
        $value = (string) new HtmlAttributesSet(new Escaper(), [
            'height' => 100,
        ]);

        self::assertSame(' height="100"', $value);
    }

    public function testEmptyStringWhenNoAttributesArePresent(): void
    {
        $value = (string) new HtmlAttributesSet(new Escaper(), []);

        self::assertSame('', $value);
    }

    public function testBooleanFalseValuesAreOmitted(): void
    {
        $value = (string) new HtmlAttributesSet(new Escaper(), [
            'some'  => false,
            'other' => 'foo',
        ]);

        self::assertSame(' other="foo"', $value);
    }

    public function testBooleanTrueValuesUseNameAsValue(): void
    {
        $value = (string) new HtmlAttributesSet(new Escaper(), [
            'some' => true,
        ]);

        self::assertSame(' some="some"', $value);
    }

    public function testUnknownArrayAttributesWillBeJoinedWithASpaceAndEmptyValuesFiltered(): void
    {
        $value = (string) new HtmlAttributesSet(new Escaper(), [
            'some' => ['a', null, '', 1, 42.5, 0, 0.0],
        ]);

        self::assertSame(' some="a&#x20;1&#x20;42.5&#x20;0&#x20;0"', $value);
    }

    public function testArrayAttributesContainingNonScalarsWillCauseAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The attribute "some" is an array, but members must be scalar');
        (string) new HtmlAttributesSet(new Escaper(), [
            'some' => ['a' => ['bad' => 'news']],
        ]);
    }

    public function testIterablesCanBeProvidedToTheConstructor(): void
    {
        $attributes = new ArrayObject(['foo' => 'bar']);
        $value      = (string) new HtmlAttributesSet(new Escaper(), $attributes);
        self::assertSame(' foo="bar"', $value);
    }

    public function testAttributeKeysAreNormalisedToLowercase(): void
    {
        $value = (string) new HtmlAttributesSet(new Escaper(), ['MUPPET' => 'Kermit']);
        self::assertSame(' muppet="Kermit"', $value);
    }
}
