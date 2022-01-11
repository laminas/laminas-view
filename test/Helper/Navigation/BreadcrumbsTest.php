<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Navigation;

use Laminas\Navigation\Navigation;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Navigation\Breadcrumbs;

use function extension_loaded;
use function strlen;
use function substr;
use function trim;

/**
 * Tests Laminas\View\Helper\Navigation\Breadcrumbs.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 * @psalm-suppress MissingConstructor
 */
class BreadcrumbsTest extends AbstractTest
{
    /**
     * View helper.
     *
     * @var Breadcrumbs
     */
    protected $_helper; // phpcs:ignore

    protected function setUp(): void
    {
        $this->_helper = new Breadcrumbs();
        parent::setUp();
    }

    public function testCanRenderStraightFromServiceAlias(): void
    {
        $this->_helper->setServiceLocator($this->serviceManager);

        $returned = $this->_helper->renderStraight('Navigation');
        $this->assertEquals($returned, $this->getExpectedFileContents('bc/default.html'));
    }

    public function testCanRenderPartialFromServiceAlias(): void
    {
        $this->_helper->setPartial('bc.phtml');
        $this->_helper->setServiceLocator($this->serviceManager);

        $returned = $this->_helper->renderPartial('Navigation');
        $this->assertEquals($returned, $this->getExpectedFileContents('bc/partial.html'));
    }

    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->_helper->__invoke();
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->nav1, $returned->getContainer());
    }

    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->_helper->__invoke($this->nav2);
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->nav2, $returned->getContainer());
    }

    public function testHelperEntryPointWithContainerStringParam(): void
    {
        $this->_helper->setServiceLocator($this->serviceManager);

        $returned = $this->_helper->__invoke('nav1');
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->nav1, $returned->getContainer());
    }

    public function testNullOutContainer(): void
    {
        $old = $this->_helper->getContainer();
        $this->_helper->setContainer();
        $new = $this->_helper->getContainer();

        $this->assertNotEquals($old, $new);
    }

    public function testSetSeparator(): void
    {
        $this->_helper->setSeparator('foo');

        $expected = $this->getExpectedFileContents('bc/separator.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testSetMaxDepth(): void
    {
        $this->_helper->setMaxDepth(1);

        $expected = $this->getExpectedFileContents('bc/maxdepth.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testSetMinDepth(): void
    {
        $this->_helper->setMinDepth(1);

        $expected = '';
        $this->assertEquals($expected, $this->_helper->render($this->nav2));
    }

    public function testLinkLastElement(): void
    {
        $this->_helper->setLinkLast(true);

        $expected = $this->getExpectedFileContents('bc/linklast.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testSetIndent(): void
    {
        $this->_helper->setIndent(8);

        $expected = '        <a';
        $actual   = substr($this->_helper->render(), 0, strlen($expected));

        $this->assertEquals($expected, $actual);
    }

    public function testRenderSuppliedContainerWithoutInterfering(): void
    {
        $this->_helper->setMinDepth(0);

        $rendered1 = $this->getExpectedFileContents('bc/default.html');
        $rendered2 = 'Site 2';

        $expected = [
            'registered'       => $rendered1,
            'supplied'         => $rendered2,
            'registered_again' => $rendered1,
        ];

        $actual = [
            'registered'       => $this->_helper->render(),
            'supplied'         => $this->_helper->render($this->nav2),
            'registered_again' => $this->_helper->render(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testUseAclResourceFromPages(): void
    {
        $acl = $this->getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);

        $expected = $this->getExpectedFileContents('bc/acl.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testTranslationUsingLaminasTranslate(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $this->_helper->setTranslator($this->getTranslator());

        $expected = $this->getExpectedFileContents('bc/translated.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testTranslationUsingLaminasTranslateAndCustomTextDomain(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $this->_helper->setTranslator($this->getTranslatorWithTextDomain());

        $expected = $this->getExpectedFileContents('bc/textdomain.html');
        $test     = $this->_helper->render($this->nav3);

        $this->assertEquals(trim($expected), trim($test));
    }

    public function testTranslationUsingLaminasTranslateAdapter(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $translator = $this->getTranslator();
        $this->_helper->setTranslator($translator);

        $expected = $this->getExpectedFileContents('bc/translated.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testDisablingTranslation(): void
    {
        $translator = $this->getTranslator();
        $this->_helper->setTranslator($translator);
        $this->_helper->setTranslatorEnabled(false);

        $expected = $this->getExpectedFileContents('bc/default.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testRenderingPartial(): void
    {
        $this->_helper->setPartial('bc.phtml');

        $expected = $this->getExpectedFileContents('bc/partial.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testRenderingPartialWithSeparator(): void
    {
        $this->_helper->setPartial('bc_separator.phtml')->setSeparator(' / ');

        $expected = trim($this->getExpectedFileContents('bc/partialwithseparator.html'));
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testRenderingPartialBySpecifyingAnArrayAsPartial(): void
    {
        $this->_helper->setPartial(['bc.phtml', 'application']);

        $expected = $this->getExpectedFileContents('bc/partial.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testRenderingPartialShouldFailOnInvalidPartialArray(): void
    {
        $this->_helper->setPartial(['bc.phtml']);
        $this->expectException(InvalidArgumentException::class);
        $this->_helper->render();
    }

    public function testRenderingPartialWithParams(): void
    {
        $this->_helper->setPartial('bc_with_partial_params.phtml')->setSeparator(' / ');
        $expected = $this->getExpectedFileContents('bc/partial_with_params.html');
        $actual   = $this->_helper->renderPartialWithParams(['variable' => 'test value']);
        $this->assertEquals($expected, $actual);
    }

    public function testLastBreadcrumbShouldBeEscaped(): void
    {
        $container = new Navigation([
            [
                'label'  => 'Live & Learn',
                'uri'    => '#',
                'active' => true,
            ],
        ]);

        $expected = 'Live &amp; Learn';
        $actual   = $this->_helper->setMinDepth(0)->render($container);

        $this->assertEquals($expected, $actual);
    }
}
