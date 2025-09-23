<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\InlineScript;
use Laminas\View\Helper\Placeholder\Position;
use PHPUnit\Framework\TestCase;

final class AbstractJavascriptHelperTest extends TestCase
{
    private InlineScript $helper;

    protected function setUp(): void
    {
        $this->helper = new InlineScript(
            new Escaper(),
            new Doctype(),
        );
    }

    private function setXhtmlDoctype(): void
    {
        $this->helper = new InlineScript(
            new Escaper(),
            new Doctype(Doctype::XHTML1_STRICT),
        );
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testDefaultStringCastIsEmpty(): void
    {
        self::assertSame('', $this->helper->__toString());
    }

    public function testAddingEmptyScriptsIsANoOp(): void
    {
        $this->helper->setScript('', ['foo' => 'bar']);
        $this->helper->appendScript('', ['foo' => 'bar']);
        $this->helper->prependScript('', ['foo' => 'bar']);

        self::assertSame('', $this->helper->__toString());
    }

    public function testExpectedOutputForAddingFiles(): void
    {
        $this->helper->setFile('a.js')
            ->appendFile('b.js')
            ->prependFile('c.js');

        $expect = <<<HTML
            <script src="c.js"></script>
            <script src="a.js"></script>
            <script src="b.js"></script>
            HTML;

        self::assertSame($expect, $this->helper->__toString());
    }

    public function testFilesHaveExpectedAttributes(): void
    {
        $this->helper->appendFile('a.js', ['defer' => true, 'async' => true, 'id' => 'foo']);

        self::assertSame(
            '<script async="async" defer="defer" id="foo" src="a.js"></script>',
            $this->helper->toString(),
        );
    }

    public function testExpectedOutputForAddingScripts(): void
    {
        $this->helper->setScript('const a = "b";')
            ->appendScript('const b = "c";')
            ->prependScript('const c = "d";');

        $expect = <<<HTML
        <script>
        const c = "d";
        </script>
        <script>
        const a = "b";
        </script>
        <script>
        const b = "c";
        </script>
        HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testTypeAttributeIsAddedForNonHtml5Doctypes(): void
    {
        $this->setXhtmlDoctype();
        $this->helper->appendFile('a.js');
        self::assertSame(
            '<script src="a.js" type="text&#x2F;javascript"></script>',
            $this->helper->toString(),
        );
    }

    public function testTypeAttributeIsNotOverriddenForNonHtml5Doctypes(): void
    {
        $this->setXhtmlDoctype();
        $this->helper->appendFile('a.js', ['type' => 'muppets']);
        self::assertSame(
            '<script src="a.js" type="muppets"></script>',
            $this->helper->toString(),
        );
    }

    public function testDuplicateSourceFilesAreOverridden(): void
    {
        $this->helper->setFile('a.js')
            ->appendFile('a.js', ['foo' => 'bar'])
            ->prependFile('a.js', ['baz' => 'bat']);
        self::assertSame(
            '<script baz="bat" src="a.js"></script>',
            $this->helper->toString(),
        );
    }

    public function testCaptureIsAppendByDefault(): void
    {
        $this->helper->appendFile('a.js');
        $this->helper->captureStart();
        echo <<<JS
            let foo = 'bar';
            foo = foo + 'baz';
            JS;
        $this->helper->captureEnd();

        $expect = <<<HTML
            <script src="a.js"></script>
            <script>
            let foo = 'bar';
            foo = foo + 'baz';
            </script>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testCaptureCanSet(): void
    {
        $this->helper->appendFile('a.js');
        $this->helper->captureStart(Position::Set, ['defer' => true]);
        echo <<<JS
            let foo = 'bar';
            foo = foo + 'baz';
            JS;
        $this->helper->captureEnd();

        $expect = <<<HTML
            <script defer="defer">
            let foo = 'bar';
            foo = foo + 'baz';
            </script>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testCaptureCanPrepend(): void
    {
        $this->helper->appendFile('a.js');
        $this->helper->captureStart(Position::Prepend, ['defer' => true]);
        echo <<<JS
            let foo = 'bar';
            foo = foo + 'baz';
            JS;
        $this->helper->captureEnd();

        $expect = <<<HTML
            <script defer="defer">
            let foo = 'bar';
            foo = foo + 'baz';
            </script>
            <script src="a.js"></script>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testIndentWithInteger(): void
    {
        $this->helper->setScript('const a = "b";')
            ->appendFile('a.js');

        $expect = <<<'HTML'
               <script>
               const a = "b";
               </script>
               <script src="a.js"></script>
            HTML;

        self::assertSame($expect, $this->helper->toString(3));
    }

    public function testIndentWithString(): void
    {
        $this->helper->setScript('const a = "b";')
            ->appendFile('a.js');

        $expect = <<<'HTML'
            Moo<script>
            Mooconst a = "b";
            Moo</script>
            Moo<script src="a.js"></script>
            HTML;

        self::assertSame($expect, $this->helper->toString('Moo'));
    }

    public function testSetSeparator(): void
    {
        $this->helper->setScript('const a = "b";')
            ->appendFile('a.js')
            ->setSeparator('!');

        $expect = <<<'HTML'
            <script>
            const a = "b";
            </script>!<script src="a.js"></script>
            HTML;

        self::assertSame($expect, $this->helper->toString());
    }

    public function testResetStateResetsDefaults(): void
    {
        $this->helper->setScript('const a = "b";')
            ->appendFile('a.js')
            ->setIndent(9)
            ->setSeparator('!')
            ->resetState();

        self::assertSame('', $this->helper->toString());
    }

    public function testResetStateAbortsCapture(): void
    {
        $this->helper->captureStart();
        echo 'Foo!';
        $this->helper->resetState();

        self::assertSame('', $this->helper->toString());

        $this->helper->captureStart();
        echo 'let x;';
        $this->helper->captureEnd();

        self::assertStringContainsString('let x;', $this->helper->toString());
    }
}
