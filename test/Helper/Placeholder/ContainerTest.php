<?php

namespace LaminasTest\View\Helper\Placeholder;

use Laminas\View\Helper\Placeholder\Container as PlaceholderContainer;
use PHPUnit\Framework\TestCase;

use function assert;
use function is_array;
use function is_int;

/**
 * Test class for Laminas\View\Helper\Placeholder\Container.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class ContainerTest extends TestCase
{
    /**
     * @var PlaceholderContainer
     */
    public $container;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->container = new PlaceholderContainer();
    }

    public function testSetSetsASingleValue(): void
    {
        $this->container['foo'] = 'bar';
        $this->container['bar'] = 'baz';
        $this->assertEquals('bar', $this->container['foo']);
        $this->assertEquals('baz', $this->container['bar']);

        $this->container->set('foo');
        $this->assertCount(1, $this->container);
        $this->assertEquals('foo', $this->container[0]);
    }

    public function testGetValueReturnsScalarWhenOneElementRegistered(): void
    {
        $this->container->set('foo');
        $this->assertEquals('foo', $this->container->getValue());
    }

    public function testGetValueReturnsArrayWhenMultipleValuesPresent(): void
    {
        $this->container['foo'] = 'bar';
        $this->container['bar'] = 'baz';
        $expected = ['foo' => 'bar', 'bar' => 'baz'];
        $this->assertEquals($expected, $this->container->getValue());
    }

    public function testPrefixAccessorsWork(): void
    {
        $this->assertEquals('', $this->container->getPrefix());
        $this->container->setPrefix('<ul><li>');
        $this->assertEquals('<ul><li>', $this->container->getPrefix());
    }

    public function testSetPrefixImplementsFluentInterface(): void
    {
        $result = $this->container->setPrefix('<ul><li>');
        $this->assertSame($this->container, $result);
    }

    public function testPostfixAccessorsWork(): void
    {
        $this->assertEquals('', $this->container->getPostfix());
        $this->container->setPostfix('</li></ul>');
        $this->assertEquals('</li></ul>', $this->container->getPostfix());
    }

    public function testSetPostfixImplementsFluentInterface(): void
    {
        $result = $this->container->setPostfix('</li></ul>');
        $this->assertSame($this->container, $result);
    }

    public function testPrependImplementsFluentInterface(): void
    {
        $result = $this->container->prepend('test');
        $this->assertSame($this->container, $result);
    }

    public function testAppendImplementsFluentInterface(): void
    {
        $result = $this->container->append('test');
        $this->assertSame($this->container, $result);
    }

    public function testSetImplementsFluentInterface(): void
    {
        $result = $this->container->set('test');
        $this->container->set('test');
        $this->assertSame($this->container, $result);
    }

    public function testSeparatorAccessorsWork(): void
    {
        $this->assertEquals('', $this->container->getSeparator());
        $this->container->setSeparator('</li><li>');
        $this->assertEquals('</li><li>', $this->container->getSeparator());
    }

    public function testSetSeparatorImplementsFluentInterface(): void
    {
        $result = $this->container->setSeparator('</li><li>');
        $this->assertSame($this->container, $result);
    }

    public function testIndentAccessorsWork(): void
    {
        $this->assertEquals('', $this->container->getIndent());
        $this->container->setIndent('    ');
        $this->assertEquals('    ', $this->container->getIndent());
        $this->container->setIndent(5);
        $this->assertEquals('     ', $this->container->getIndent());
    }

    public function testSetIndentImplementsFluentInterface(): void
    {
        $result = $this->container->setIndent('    ');
        $this->assertSame($this->container, $result);
    }

    public function testCapturingToPlaceholderStoresContent(): void
    {
        $this->container->captureStart();
        echo 'This is content intended for capture';
        $this->container->captureEnd();
        $this->assertStringContainsString(
            'This is content intended for capture',
            (string) $this->container->getValue()
        );
    }

    public function testCapturingToPlaceholderAppendsContent(): void
    {
        $this->container[] = 'foo';
        $originalCount = count($this->container);

        $this->container->captureStart();
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertCount($originalCount + 1, $this->container);

        $value     = $this->container->getValue();
        assert(is_array($value));
        $keys      = array_keys($value);
        $lastIndex = array_pop($keys);
        assert(is_int($lastIndex));
        $this->assertEquals('foo', $value[$lastIndex - 1]);
        $this->assertStringContainsString(
            'This is content intended for capture',
            (string) $value[$lastIndex]
        );
    }

    public function testCapturingToPlaceholderUsingPrependPrependsContent(): void
    {
        $this->container[] = 'foo';
        $originalCount = count($this->container);

        $this->container->captureStart('PREPEND');
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertCount($originalCount + 1, $this->container);

        $value     = $this->container->getValue();
        assert(is_array($value));
        $keys      = array_keys($value);
        $lastIndex = array_pop($keys);
        assert(is_int($lastIndex));
        $this->assertEquals('foo', $value[$lastIndex]);
        $this->assertStringContainsString('This is content intended for capture', (string) $value[$lastIndex - 1]);
    }

    public function testCapturingToPlaceholderUsingSetOverwritesContent(): void
    {
        $this->container[] = 'foo';
        $this->container->captureStart('SET');
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertCount(1, $this->container);

        $this->assertStringContainsString(
            'This is content intended for capture',
            (string) $this->container->getValue()
        );
    }

    public function testCapturingToPlaceholderKeyUsingSetCapturesContent(): void
    {
        $this->container->captureStart('SET', 'key');
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertCount(1, $this->container);
        $this->assertTrue(isset($this->container['key']));
        $this->assertStringContainsString(
            'This is content intended for capture',
            (string) $this->container['key']
        );
    }

    public function testCapturingToPlaceholderKeyUsingSetReplacesContentAtKey(): void
    {
        $this->container['key'] = 'Foobar';
        $this->container->captureStart('SET', 'key');
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertCount(1, $this->container);
        $this->assertTrue(isset($this->container['key']));
        $value = (string) $this->container['key'];
        $this->assertStringContainsString('This is content intended for capture', $value);
    }

    public function testCapturingToPlaceholderKeyUsingAppendAppendsContentAtKey(): void
    {
        $this->container['key'] = 'Foobar ';
        $this->container->captureStart('APPEND', 'key');
        echo 'This is content intended for capture';
        $this->container->captureEnd();

        $this->assertCount(1, $this->container);
        $this->assertTrue(isset($this->container['key']));
        $value = (string) $this->container['key'];
        $this->assertStringContainsString('Foobar This is content intended for capture', $value);
    }

    public function testNestedCapturesThrowsException(): void
    {
        $this->container[] = 'foo';
        $caught = false;
        try {
            $this->container->captureStart('SET');
            $this->container->captureStart('SET');
            $this->container->captureEnd();
            $this->container->captureEnd();
        } catch (\Exception $e) {
            $this->container->captureEnd();
            $caught = true;
        }

        $this->assertTrue($caught, 'Nested captures should throw exceptions');
    }

    public function testToStringWithNoModifiersAndSingleValueReturnsValue(): void
    {
        $this->container->set('foo');
        $value = $this->container->toString();
        $this->assertEquals($this->container->getValue(), $value);
    }

    public function testToStringWithModifiersAndSingleValueReturnsFormattedValue(): void
    {
        $this->container->set('foo');
        $this->container->setPrefix('<li>')
                        ->setPostfix('</li>');
        $value = $this->container->toString();
        $this->assertEquals('<li>foo</li>', $value);
    }

    public function testToStringWithNoModifiersAndCollectionReturnsImplodedString(): void
    {
        $this->container[] = 'foo';
        $this->container[] = 'bar';
        $this->container[] = 'baz';
        $value = $this->container->toString();
        $this->assertEquals('foobarbaz', $value);
    }

    public function testToStringWithModifiersAndCollectionReturnsFormattedString(): void
    {
        $this->container[] = 'foo';
        $this->container[] = 'bar';
        $this->container[] = 'baz';
        $this->container->setPrefix('<ul><li>')
                        ->setSeparator('</li><li>')
                        ->setPostfix('</li></ul>');
        $value = $this->container->toString();
        $this->assertEquals('<ul><li>foo</li><li>bar</li><li>baz</li></ul>', $value);
    }

    public function testToStringWithModifiersAndCollectionReturnsFormattedStringWithIndentation(): void
    {
        $this->container[] = 'foo';
        $this->container[] = 'bar';
        $this->container[] = 'baz';
        $this->container->setPrefix('<ul><li>')
                        ->setSeparator('</li>' . PHP_EOL . '<li>')
                        ->setPostfix('</li></ul>')
                        ->setIndent('    ');
        $value = $this->container->toString();
        $expectedValue = '    <ul><li>foo</li>' . PHP_EOL . '    <li>bar</li>' . PHP_EOL . '    <li>baz</li></ul>';
        $this->assertEquals($expectedValue, $value);
    }

    public function testToStringProxiesToToString(): void
    {
        $this->container[] = 'foo';
        $this->container[] = 'bar';
        $this->container[] = 'baz';
        $this->container->setPrefix('<ul><li>')
                        ->setSeparator('</li><li>')
                        ->setPostfix('</li></ul>');
        $value = $this->container->__toString();
        $this->assertEquals('<ul><li>foo</li><li>bar</li><li>baz</li></ul>', $value);
    }

    public function testPrependPushesValueToTopOfContainer(): void
    {
        $this->container['foo'] = 'bar';
        $this->container->prepend('baz');

        $expected = ['baz', 'foo' => 'bar'];
        $array = $this->container->getArrayCopy();
        $this->assertSame($expected, $array);
    }

    public function testIndentationIsHonored(): void
    {
        $this->container->setIndent(4)
                        ->setPrefix("<ul>\n    <li>")
                        ->setSeparator("</li>\n    <li>")
                        ->setPostfix("</li>\n</ul>");
        $this->container->append('foo');
        $this->container->append('bar');
        $this->container->append('baz');
        $string = $this->container->toString();

        $lis = substr_count($string, "\n        <li>");
        $this->assertEquals(3, $lis);
        $this->assertStringContainsString("    <ul>\n", $string, $string);
        $this->assertStringContainsString("\n    </ul>", $string, $string);
    }

    /**
     * @see https://github.com/zendframework/zend-view/pull/133
     */
    public function testNoPrefixOrPostfixAreRenderedIfNoItemsArePresentInTheContainer(): void
    {
        $this->container
            ->setPrefix("<h1>")
            ->setPostfix("</h1>");
        $string = $this->container->toString();
        $this->assertEquals('', $string);

        $this->container->set('');
        $string = $this->container->toString();
        $this->assertEquals('<h1></h1>', $string);
    }
}
