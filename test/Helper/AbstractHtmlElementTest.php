<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Renderer\PhpRenderer;
use LaminasTest\View\Helper\TestAsset\ConcreteElementHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function sprintf;

#[CoversClass(AbstractHtmlElement::class)]
class AbstractHtmlElementTest extends TestCase
{
    /** @var ConcreteElementHelper */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = new ConcreteElementHelper();
        $this->helper->setView(new PhpRenderer());
    }

    public function testWillEscapeValueAttributeValuesCorrectly(): void
    {
        self::assertEquals(
            ' data-value="breaking&#x20;your&#x20;HTML&#x20;like&#x20;a&#x20;boss&#x21;&#x20;&#x5C;"',
            $this->helper->compileAttributes(['data-value' => 'breaking your HTML like a boss! \\'])
        );
    }

    public function testThatAttributesWithANullValueArePresentedAsAnEmptyString(): void
    {
        $expect = 'something=""';
        self::assertStringContainsString($expect, $this->helper->compileAttributes(['something' => null]));
    }

    /** @param scalar|scalar[]|null $attributeValue */
    #[DataProvider('attributeValuesProvider')]
    public function testThatAttributesOfVariousNativeTypesProduceTheExpectedAttributeString(
        $attributeValue,
        string $expected,
        string $expectedEventValue
    ): void {
        $expect = sprintf('atr=%s', $expected);
        self::assertStringContainsString($expect, $this->helper->compileAttributes(['atr' => $attributeValue]));

        $expect = sprintf('onclick=%s', $expectedEventValue);
        self::assertStringContainsString($expect, $this->helper->compileAttributes(['onclick' => $attributeValue]));
    }

    /** @return array<string, array{0: scalar|scalar[]|null, 1: string, 2: string}> */
    public static function attributeValuesProvider(): array
    {
        return [
            'Integer'    => [1, '"1"', '"1"'],
            'Float'      => [0.5, '"0.5"', '"0.5"'],
            'String'     => ['whatever', '"whatever"', '"whatever"'],
            'Null'       => [null, '""', '"null"'],
            'Class List' => [
                ['foo', 'bar', 'baz'],
                '"foo&#x20;bar&#x20;baz"',
                '"&#x5B;&quot;foo&quot;,&quot;bar&quot;,&quot;baz&quot;&#x5D;"',
            ],
        ];
    }
}
