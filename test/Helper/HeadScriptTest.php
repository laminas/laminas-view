<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use DOMDocument;
use Generator;
use Laminas\Escaper\Escaper;
use Laminas\View;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadScript;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function assert;
use function sprintf;
use function strtolower;
use function substr_count;
use function ucfirst;
use function var_export;

use const PHP_EOL;

class HeadScriptTest extends TestCase
{
    private HeadScript $helper;
    private Escaper $escaper;

    protected function setUp(): void
    {
        $this->helper  = new HeadScript();
        $this->escaper = new Escaper();
    }

    public function testHeadScriptReturnsObjectInstance(): void
    {
        $placeholder = $this->helper->__invoke();
        $this->assertInstanceOf(HeadScript::class, $placeholder);
    }

    public function testAppendThrowsExceptionWithInvalidArguments(): void
    {
        $this->expectException(View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument passed to append');
        /** @psalm-suppress InvalidArgument */
        $this->helper->append('foo');
    }

    public function testPrependThrowsExceptionWithInvalidArguments(): void
    {
        $this->expectException(View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument passed to prepend');
        /** @psalm-suppress InvalidArgument */
        $this->helper->prepend('foo');
    }

    public function testSetThrowsExceptionWithInvalidArguments(): void
    {
        $this->expectException(View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument passed to set');
        /** @psalm-suppress InvalidArgument */
        $this->helper->set('foo');
    }

    public function testOffsetSetThrowsExceptionWithInvalidArguments(): void
    {
        $this->expectException(View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument passed to offsetSet');
        /** @psalm-suppress InvalidArgument */
        $this->helper->offsetSet(1, 'foo');
    }

    private function inflectAction(string $type): string
    {
        return ucfirst(strtolower($type));
    }

    private function executeOverloadAppend(string $type): void
    {
        $action = 'append' . $this->inflectAction($type);
        $string = 'foo';
        for ($i = 0; $i < 3; ++$i) {
            $string .= ' foo';
            $this->helper->$action($string);
            $values = $this->helper->getContainer()->getArrayCopy();
            self::assertCount($i + 1, $values);
            $item = $values[$i];
            if ('file' === $type) {
                self::assertEquals($string, $item->attributes['src']);
            } elseif ('script' === $type) {
                self::assertEquals($string, $item->source);
            }
            self::assertEquals('text/javascript', $item->type);
        }
    }

    private function executeOverloadPrepend(string $type): void
    {
        $action = 'prepend' . $this->inflectAction($type);
        $string = 'foo';
        for ($i = 0; $i < 3; ++$i) {
            $string .= ' foo';
            $this->helper->$action($string);
            $values = $this->helper->getContainer()->getArrayCopy();
            self::assertCount($i + 1, $values);
            $first = array_shift($values);
            self::assertIsObject($first);
            if ('file' === $type) {
                self::assertEquals($string, $first->attributes['src']);
            } elseif ('script' === $type) {
                self::assertEquals($string, $first->source);
            }
            self::assertEquals('text/javascript', $first->type);
        }
    }

    private function executeOverloadSet(string $type): void
    {
        $action = 'set' . $this->inflectAction($type);
        $string = 'foo';
        for ($i = 0; $i < 3; ++$i) {
            $this->helper->appendScript($string);
            $string .= ' foo';
        }
        $this->helper->$action($string);
        $values = $this->helper->getContainer()->getArrayCopy();
        self::assertCount(1, $values);
        $item = $values[0];
        if ('file' === $type) {
            self::assertEquals($string, $item->attributes['src']);
        } elseif ('script' === $type) {
            self::assertEquals($string, $item->source);
        }
        self::assertEquals('text/javascript', $item->type);
    }

    private function executeOverloadOffsetSet(string $type): void
    {
        $action = 'offsetSet' . $this->inflectAction($type);
        $string = 'foo';
        $this->helper->$action(5, $string);
        $values = $this->helper->getContainer()->getArrayCopy();
        self::assertCount(1, $values);
        $item = $values[5];
        if ('file' === $type) {
            self::assertEquals($string, $item->attributes['src']);
        } elseif ('script' === $type) {
            self::assertEquals($string, $item->source);
        }
        self::assertEquals('text/javascript', $item->type);
    }

    public function testOverloadAppendFileAppendsScriptsToStack(): void
    {
        $this->executeOverloadAppend('file');
    }

    public function testOverloadAppendScriptAppendsScriptsToStack(): void
    {
        $this->executeOverloadAppend('script');
    }

    public function testOverloadPrependFileAppendsScriptsToStack(): void
    {
        $this->executeOverloadPrepend('file');
    }

    public function testOverloadPrependScriptAppendsScriptsToStack(): void
    {
        $this->executeOverloadPrepend('script');
    }

    public function testOverloadSetFileOverwritesStack(): void
    {
        $this->executeOverloadSet('file');
    }

    public function testOverloadSetScriptOverwritesStack(): void
    {
        $this->executeOverloadSet('script');
    }

    public function testOverloadOffsetSetFileWritesToSpecifiedIndex(): void
    {
        $this->executeOverloadOffsetSet('file');
    }

    public function testOverloadOffsetSetScriptWritesToSpecifiedIndex(): void
    {
        $this->executeOverloadOffsetSet('script');
    }

    public function testOverloadingThrowsExceptionWithInvalidMethod(): void
    {
        $this->expectException(View\Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "fooBar" does not exist');
        /** @psalm-suppress UndefinedMagicMethod */
        $this->helper->fooBar('foo');
    }

    public function testSetScriptRequiresAnArgument(): void
    {
        $this->expectException(View\Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "setScript" requires at least one argument');
        /** @psalm-suppress TooFewArguments */
        $this->helper->setScript();
    }

    public function testOffsetSetScriptRequiresTwoArguments(): void
    {
        $this->expectException(View\Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "offsetSetScript" requires at least two arguments, an index and source');
        /** @psalm-suppress TooFewArguments */
        $this->helper->offsetSetScript(1);
    }

    public function testHeadScriptAppropriatelySetsScriptItems(): void
    {
        $this->helper->__invoke('FILE', 'foo', 'set')
                     ->__invoke('SCRIPT', 'bar', 'prepend')
                     ->__invoke('SCRIPT', 'baz', 'append');
        $items = $this->helper->getContainer()->getArrayCopy();
        for ($i = 0; $i < 3; ++$i) {
            $item = $items[$i];
            switch ($i) {
                case 0:
                    $this->assertObjectHasProperty('source', $item);
                    $this->assertEquals('bar', $item->source);
                    break;
                case 1:
                    $this->assertObjectHasProperty('attributes', $item);
                    $this->assertTrue(isset($item->attributes['src']));
                    $this->assertEquals('foo', $item->attributes['src']);
                    break;
                case 2:
                    $this->assertObjectHasProperty('source', $item);
                    $this->assertEquals('baz', $item->source);
                    break;
            }
        }
    }

    public function testToStringRendersValidHtml(): void
    {
        $this->helper->__invoke('FILE', 'foo', 'set')
                     ->__invoke('SCRIPT', 'bar', 'prepend')
                     ->__invoke('SCRIPT', 'baz', 'append');
        $string = $this->helper->toString();

        $scripts = substr_count($string, '<script ');
        $this->assertEquals(3, $scripts);
        $scripts = substr_count($string, '</script>');
        $this->assertEquals(3, $scripts);
        $scripts = substr_count($string, 'src="');
        $this->assertEquals(1, $scripts);
        $scripts = substr_count($string, '><');
        $this->assertEquals(1, $scripts);

        $this->assertStringContainsString('src="foo"', $string);
        $this->assertStringContainsString('bar', $string);
        $this->assertStringContainsString('baz', $string);

        assert($string !== '');

        $doc = new DOMDocument();
        $dom = $doc->loadHtml($string);
        $this->assertTrue($dom);
    }

    public function testCapturingCapturesToObject(): void
    {
        $this->helper->captureStart();
        echo 'foobar';
        $this->helper->captureEnd();
        $values = $this->helper->getContainer()->getArrayCopy();
        $this->assertCount(1, $values, var_export($values, true));
        $item = array_shift($values);
        $this->assertStringContainsString('foobar', $item->source);
    }

    public function testIndentationIsHonored(): void
    {
        $this->helper->setIndent(4);
        $this->helper->appendScript('
var foo = "bar";
    document.write(foo.strlen());');
        $this->helper->appendScript('
var bar = "baz";
document.write(bar.strlen());');
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <script');
        $this->assertEquals(2, $scripts);
        $this->assertStringContainsString('    //', $string);
        $this->assertStringContainsString('var', $string);
        $this->assertStringContainsString('document', $string);
        $this->assertStringContainsString('    document', $string);
    }

    public function testDoesNotAllowDuplicateFiles(): void
    {
        $this->helper->__invoke('FILE', '/js/prototype.js');
        $this->helper->__invoke('FILE', '/js/prototype.js');
        $this->assertCount(1, $this->helper);
    }

    public function testRenderingDoesNotRenderArbitraryAttributesByDefault(): void
    {
        $this->helper->__invoke()->appendFile('/js/foo.js', 'text/javascript', ['bogus' => 'deferred']);
        $test = $this->helper->__invoke()->toString();
        $this->assertStringNotContainsString('bogus="deferred"', $test);
    }

    public function testCanRenderArbitraryAttributesOnRequest(): void
    {
        $this->helper->__invoke()->appendFile('/js/foo.js', 'text/javascript', ['bogus' => 'deferred'])
             ->setAllowArbitraryAttributes(true);
        $test = $this->helper->__invoke()->toString();
        $this->assertStringContainsString('bogus="deferred"', $test);
    }

    public function testCanPerformMultipleSerialCaptures(): void
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

    public function testCannotNestCaptures(): void
    {
        $this->helper->__invoke()->captureStart();
        echo "this is something captured";
        try {
            $this->helper->__invoke()->captureStart();
            $this->helper->__invoke()->captureEnd();
            $this->fail('Should not be able to nest captures');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->helper->__invoke()->captureEnd();
            $this->assertStringContainsString('Cannot nest', $e->getMessage());
        }
    }

    public function testTurnOffAutoEscapeDoesNotEncodeAmpersand(): void
    {
        $this->helper->setAutoEscape(false)->appendFile('test.js?id=123&foo=bar');
        $this->assertEquals(
            '<script type="text/javascript" src="test.js?id=123&foo=bar"></script>',
            $this->helper->toString()
        );
    }

    public function testConditionalScript(): void
    {
        $this->helper->__invoke()->appendFile('/js/foo.js', 'text/javascript', ['conditional' => 'lt IE 7']);
        $test = $this->helper->__invoke()->toString();
        $this->assertStringContainsString('<!--[if lt IE 7]>', $test);
    }

    public function testConditionalScriptWidthIndentation(): void
    {
        $this->helper->__invoke()->appendFile('/js/foo.js', 'text/javascript', ['conditional' => 'lt IE 7']);
        $this->helper->__invoke()->setIndent(4);
        $test = $this->helper->__invoke()->toString();
        $this->assertStringContainsString('    <!--[if lt IE 7]>', $test);
    }

    public function testConditionalScriptNoIE(): void
    {
        $this->helper->setAllowArbitraryAttributes(true);
        $this->helper->appendFile(
            '/js/foo.js',
            'text/javascript',
            ['conditional' => '!IE']
        );
        $test = $this->helper->toString();

        $this->assertStringContainsString('<!--[if !IE]><!--><', $test);
        $this->assertStringContainsString('<!--<![endif]-->', $test);
    }

    public function testConditionalScriptNoIEWidthSpace(): void
    {
        $this->helper->setAllowArbitraryAttributes(true);
        $this->helper->appendFile(
            '/js/foo.js',
            'text/javascript',
            ['conditional' => '! IE']
        );
        $test = $this->helper->toString();

        $this->assertStringContainsString('<!--[if ! IE]><!--><', $test);
        $this->assertStringContainsString('<!--<![endif]-->', $test);
    }

    public function testContainerMaintainsCorrectOrderOfItems(): void
    {
        $this->helper->offsetSetFile(1, 'test1.js');
        $this->helper->offsetSetFile(20, 'test2.js');
        $this->helper->offsetSetFile(10, 'test3.js');
        $this->helper->offsetSetFile(5, 'test4.js');

        $test = $this->helper->toString();

        $expected = sprintf(
            '<script type="%2$s" src="%3$s"></script>%1$s'
            . '<script type="%2$s" src="%4$s"></script>%1$s'
            . '<script type="%2$s" src="%5$s"></script>%1$s'
            . '<script type="%2$s" src="%6$s"></script>',
            PHP_EOL,
            $this->escaper->escapeHtmlAttr('text/javascript'),
            $this->escaper->escapeHtmlAttr('test1.js'),
            $this->escaper->escapeHtmlAttr('test4.js'),
            $this->escaper->escapeHtmlAttr('test3.js'),
            $this->escaper->escapeHtmlAttr('test2.js')
        );

        $this->assertEquals($expected, $test);
    }

    public function testConditionalWithAllowArbitraryAttributesDoesNotIncludeConditionalScript(): void
    {
        $this->helper->__invoke()->setAllowArbitraryAttributes(true);
        $this->helper->__invoke()->appendFile('/js/foo.js', 'text/javascript', ['conditional' => 'lt IE 7']);
        $test = $this->helper->__invoke()->toString();

        $this->assertStringNotContainsString('conditional', $test);
    }

    public function testNoEscapeWithAllowArbitraryAttributesDoesNotIncludeNoEscapeScript(): void
    {
        $this->helper->__invoke()->setAllowArbitraryAttributes(true);
        $this->helper->__invoke()->appendScript('// some script', 'text/javascript', ['noescape' => true]);
        $test = $this->helper->__invoke()->toString();

        $this->assertStringNotContainsString('noescape', $test);
    }

    public function testNoEscapeDefaultsToFalse(): void
    {
        $this->helper->__invoke()->appendScript('// some script' . PHP_EOL, 'text/javascript', []);
        $test = $this->helper->__invoke()->toString();

        $this->assertStringContainsString('//<!--', $test);
        $this->assertStringContainsString('//-->', $test);
    }

    public function testNoEscapeTrue(): void
    {
        $this->helper->__invoke()->appendScript('// some script' . PHP_EOL, 'text/javascript', ['noescape' => true]);
        $test = $this->helper->__invoke()->toString();

        $this->assertStringNotContainsString('//<!--', $test);
        $this->assertStringNotContainsString('//-->', $test);
    }

    public function testSupportsCrossOriginAttribute(): void
    {
        $this->helper->__invoke()->appendScript(
            '// some script' . PHP_EOL,
            'text/javascript',
            ['crossorigin' => true]
        );
        $test = $this->helper->__invoke()->toString();

        $this->assertStringContainsString('crossorigin="', $test);
    }

    public function testOmitsTypeAttributeIfEmptyValueAndHtml5Doctype(): void
    {
        $view = new View\Renderer\PhpRenderer();
        $view->plugin(Doctype::class)->setDoctype(View\Helper\Doctype::HTML5);
        $this->helper->setView($view);

        $this->helper->__invoke()->appendScript('// some script' . PHP_EOL, '');
        $test = $this->helper->__invoke()->toString();
        $this->assertStringNotContainsString('type', $test);
    }

    public function testSupportsAsyncAttribute(): void
    {
        $this->helper->__invoke()->appendScript(
            '// some script' . PHP_EOL,
            'text/javascript',
            ['async' => true]
        );
        $test = $this->helper->__invoke()->toString();
        $this->assertStringContainsString('async="', $test);
    }

    public function testOmitsTypeAttributeIfNoneGivenAndHtml5Doctype(): void
    {
        $view = new View\Renderer\PhpRenderer();
        $view->plugin(Doctype::class)->setDoctype(View\Helper\Doctype::HTML5);
        $this->helper->setView($view);

        $this->helper->__invoke()->appendScript('// some script' . PHP_EOL);
        $test = $this->helper->__invoke()->toString();
        $this->assertDoesNotMatchRegularExpression('#type="text/javascript"#i', $test);
    }

    public function testSupportsNonceAttribute(): void
    {
        ($this->helper)()->appendScript(
            '// some js',
            'text/javascript',
            ['nonce' => 'random']
        );

        self::assertStringContainsString(
            'nonce="random"',
            (string) ($this->helper)()
        );
    }

    /** @return Generator<string, array<int, string>> */
    public static function booleanAttributeDataProvider(): Generator
    {
        $values = ['async', 'defer', 'nomodule'];

        foreach ($values as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('booleanAttributeDataProvider')]
    public function testBooleanAttributesUseTheKeyNameAsTheValue(string $attribute): void
    {
        ($this->helper)()->appendScript(
            '// some js',
            'text/javascript',
            [$attribute => 'whatever']
        );

        self::assertStringContainsString(
            sprintf('%1$s="%1$s"', $attribute),
            (string) ($this->helper)()
        );
    }

    #[DataProvider('booleanAttributeDataProvider')]
    public function testBooleanAttributesCanBeAppliedToModules(string $attribute): void
    {
        ($this->helper)()->appendScript(
            '// some js',
            'module',
            [$attribute => 'whatever']
        );

        self::assertStringContainsString(
            sprintf('%1$s="%1$s"', $attribute),
            (string) ($this->helper)()
        );

        self::assertStringContainsString(
            'type="module"',
            (string) ($this->helper)()
        );
    }
}
