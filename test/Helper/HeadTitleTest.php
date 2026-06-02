<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Translator\TranslatorInterface;
use Laminas\View\Helper\HeadTitle;
use LaminasTest\View\TestAsset\TranslatorStubFactory;
use PHPUnit\Framework\TestCase;

final class HeadTitleTest extends TestCase
{
    /** @var HeadTitle */
    public $helper;

    protected function setUp(): void
    {
        $this->helper = new HeadTitle();
    }

    public function testInvokeWithNoArgumentsReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testCanSetTitleViaHeadTitle(): void
    {
        $placeholder = $this->helper->__invoke('Foo Bar');
        self::assertEquals('Foo Bar', $placeholder->renderTitle());
    }

    public function testToStringWrapsToTitleTag(): void
    {
        $placeholder = $this->helper->__invoke('Foo Bar');
        self::assertEquals('<title>Foo Bar</title>', $placeholder->toString());
    }

    public function testCanAppendTitleViaHeadTitle(): void
    {
        $this->helper->__invoke('Foo');
        $this->helper->__invoke('Bar');
        self::assertEquals('FooBar', $this->helper->renderTitle());
    }

    public function testCanPrependTitleViaHeadTitle(): void
    {
        $helper = new HeadTitle();
        $helper->append('Foo');
        $helper->prepend('Bar');

        self::assertEquals('BarFoo', $helper->renderTitle());
    }

    public function testReturnedPlaceholderRenderTitleContainsFullTitleElement(): void
    {
        $this->helper->append('Foo');
        $this->helper->append('Bar');
        $this->helper->setSeparator(' :: ');
        self::assertEquals('Foo :: Bar', $this->helper->renderTitle());
    }

    public function testSetOverwritesExistingValues(): void
    {
        $helper = new HeadTitle();
        $helper->append('Foo')
            ->append('Bar')
            ->set('Baz');
        self::assertSame('Baz', $helper->renderTitle());
    }

    public function testRenderTitleEscapesEntries(): void
    {
        $this->helper->__invoke('<script type="text/javascript">alert("foo");</script>');
        $string = $this->helper->renderTitle();
        $this->assertStringNotContainsString('<script', $string);
        $this->assertStringNotContainsString('</script>', $string);
    }

    public function testRenderTitleEscapesSeparator(): void
    {
        $this->helper->__invoke('Foo')
                     ->__invoke('Bar')
                     ->setSeparator(' <br /> ');
        $string = $this->helper->renderTitle();
        $this->assertStringNotContainsString('<br />', $string);
        $this->assertStringContainsString('Foo', $string);
        $this->assertStringContainsString('Bar', $string);
        $this->assertStringContainsString('&lt;br /&gt;', $string);
    }

    public function testIndentationIsHonored(): void
    {
        $this->helper->setIndent(4);
        $this->helper->__invoke('foo');
        $string = $this->helper->toString();

        $this->assertStringContainsString('    <title>', $string);
    }

    public function testAutoEscapeIsHonored(): void
    {
        $helper = new HeadTitle();
        $helper->append('Some Title &copyright;');
        $this->assertEquals('Some Title &amp;copyright;', $helper->renderTitle());

        $helper = new HeadTitle(null, false);
        $helper->append('Some Title &copyright;');
        $this->assertEquals('Some Title &copyright;', $helper->renderTitle());
    }

    public function testThatAPrefixAndPostfixCanBeApplied(): void
    {
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix: ');
        $this->helper->setPostfix(' :Postfix');

        $this->assertEquals('Prefix: Some Title :Postfix', $this->helper->renderTitle());
    }

    public function testThatPrefixAndPostfixAreEscapedProperly(): void
    {
        $this->helper->__invoke('Some Title');
        $this->helper->setPrefix('Prefix & ');
        $this->helper->setPostfix(' & Postfix');

        $this->assertEquals('Prefix &amp; Some Title &amp; Postfix', $this->helper->renderTitle());
    }

    private function getTranslator(): TranslatorInterface
    {
        $matcher = fn (string $message): string => match ($message) {
            'Foo' => 'Kermit',
            'Bar' => 'Fozzy Bear',
            default => 'Gonzo',
        };

        return (new TranslatorStubFactory())->getTranslator($matcher);
    }

    public function testCanTranslateTitle(): void
    {
        $translator = $this->getTranslator();
        $helper     = new HeadTitle(
            null,
            true,
            ' - ',
            '',
            '',
            '',
            $translator,
        );

        $expect = '<title>Kermit - Fozzy Bear - Gonzo</title>';

        $helper->append('Foo')
            ->append('Bar')
            ->append('Baz');

        self::assertSame($expect, $helper->__toString());
    }

    public function testHeadTitleZero(): void
    {
        $this->helper->__invoke('0');
        $this->assertEquals('0', $this->helper->renderTitle());
    }

    public function testCommonMagicMethods(): void
    {
        $this->helper->set('a little');
        $this->helper->prepend('Mary had');
        $this->helper->append('lamb');
        $this->helper->setSeparator(' ');

        self::assertSame('<title>Mary had a little lamb</title>', (string) $this->helper);
    }

    public function testExpectedBehaviourForResetState(): void
    {
        $helper = new HeadTitle(null, true, ' - ', "\t", 'Pre', 'Post');

        self::assertSame("\t" . '<title>PrePost</title>', $helper->__toString());

        $helper->append('Title');
        $helper->append('1');

        self::assertSame("\t" . '<title>PreTitle - 1Post</title>', $helper->__toString());

        $helper->setIndent(' ')
            ->setSeparator(' : ')
            ->setPrefix('Foo')
            ->setPostfix('Bar');

        self::assertSame(' <title>FooTitle : 1Bar</title>', $helper->__toString());

        $helper->resetState();

        self::assertSame("\t" . '<title>PrePost</title>', $helper->__toString());
    }
}
