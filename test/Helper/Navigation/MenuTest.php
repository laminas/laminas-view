<?php

namespace LaminasTest\View\Helper\Navigation;

use const PHP_EOL;

use Laminas\Navigation\Navigation;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Navigation\Menu;

use function count;
use function extension_loaded;
use function rtrim;
use function str_replace;
use function trim;

/**
 * Tests Laminas\View\Helper\Navigation\Menu.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 *
 * @psalm-suppress MissingConstructor
 */
class MenuTest extends AbstractTest
{
    // @codingStandardsIgnoreStart
    /**
     * View helper.
     *
     * @var Menu
     */
    protected $_helper;
    // @codingStandardsIgnoreEnd

    protected function setUp(): void
    {
        $this->_helper = new Menu();
        parent::setUp();
    }

    public function testCanRenderMenuFromServiceAlias(): void
    {
        $this->_helper->setServiceLocator($this->serviceManager);

        $returned = $this->_helper->renderMenu('Navigation');
        $this->assertEquals($returned, $this->_getExpected('menu/default1.html'));
    }

    public function testCanRenderPartialFromServiceAlias(): void
    {
        $this->_helper->setPartial('menu.phtml');
        $this->_helper->setServiceLocator($this->serviceManager);

        $returned = $this->_helper->renderPartial('Navigation');
        $this->assertEquals($returned, $this->_getExpected('menu/partial.html'));
    }

    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->_helper->__invoke();
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->_nav1, $returned->getContainer());
    }

    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->_helper->__invoke($this->_nav2);
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->_nav2, $returned->getContainer());
    }

    public function testNullingOutContainerInHelper(): void
    {
        $this->_helper->setContainer();
        $this->assertEquals(0, count($this->_helper->getContainer()));
    }

    public function testSetIndentAndOverrideInRenderMenu(): void
    {
        $this->_helper->setIndent(8);

        $expected = [
            'indent4' => $this->_getExpected('menu/indent4.html'),
            'indent8' => $this->_getExpected('menu/indent8.html'),
        ];

        $renderOptions = [
            'indent' => 4,
        ];

        $actual = [
            'indent4' => rtrim($this->_helper->renderMenu(null, $renderOptions), PHP_EOL),
            'indent8' => rtrim($this->_helper->renderMenu(), PHP_EOL),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testRenderSuppliedContainerWithoutInterfering(): void
    {
        $rendered1 = $this->_getExpected('menu/default1.html');
        $rendered2 = $this->_getExpected('menu/default2.html');
        $expected = [
            'registered'       => $rendered1,
            'supplied'         => $rendered2,
            'registered_again' => $rendered1,
        ];

        $actual = [
            'registered'       => $this->_helper->render(),
            'supplied'         => $this->_helper->render($this->_nav2),
            'registered_again' => $this->_helper->render(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testUseAclRoleAsString(): void
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole('member');

        $expected = $this->_getExpected('menu/acl_string.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testFilterOutPagesBasedOnAcl(): void
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);

        $expected = $this->_getExpected('menu/acl.html');
        $actual = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testDisablingAcl(): void
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);
        $this->_helper->setUseAcl(false);

        $expected = $this->_getExpected('menu/default1.html');
        $actual = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testUseAnAclRoleInstanceFromAclObject(): void
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['acl']->getRole('member'));

        $expected = $this->_getExpected('menu/acl_role_interface.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testUseConstructedAclRolesNotFromAclObject(): void
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole(new \Laminas\Permissions\Acl\Role\GenericRole('member'));

        $expected = $this->_getExpected('menu/acl_role_interface.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testSetUlCssClass(): void
    {
        $this->_helper->setUlClass('My_Nav');
        $expected = $this->_getExpected('menu/css.html');
        $this->assertEquals($expected, $this->_helper->render($this->_nav2));
    }

    public function testSetLiActiveCssClass(): void
    {
        $this->_helper->setLiActiveClass('activated');
        $expected = $this->_getExpected('menu/css2.html');
        $this->assertEquals(trim($expected), $this->_helper->render($this->_nav2));
    }

    public function testOptionEscapeLabelsAsTrue(): void
    {
        $options = [
            'escapeLabels' => true,
        ];

        $container = new Navigation($this->_nav2->toArray());
        $container->addPage([
            'label' => 'Badges <span class="badge">1</span>',
            'uri' => 'badges',
        ]);

        $expected = $this->_getExpected('menu/escapelabels_as_true.html');
        $actual = $this->_helper->renderMenu($container, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionEscapeLabelsAsFalse(): void
    {
        $options = [
            'escapeLabels' => false,
        ];

        $container = new Navigation($this->_nav2->toArray());
        $container->addPage([
            'label' => 'Badges <span class="badge">1</span>',
            'uri' => 'badges',
        ]);

        $expected = $this->_getExpected('menu/escapelabels_as_false.html');
        $actual = $this->_helper->renderMenu($container, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testTranslationUsingLaminasTranslate(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $translator = $this->_getTranslator();
        $this->_helper->setTranslator($translator);

        $expected = $this->_getExpected('menu/translated.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testTranslationUsingLaminasTranslateWithTextDomain(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $translator = $this->_getTranslatorWithTextDomain();
        $this->_helper->setTranslator($translator);

        $expected = $this->_getExpected('menu/textdomain.html');
        $test     = $this->_helper->render($this->_nav3);
        $this->assertEquals(trim($expected), trim($test));
    }

    public function testTranslationUsingLaminasTranslateAdapter(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $translator = $this->_getTranslator();
        $this->_helper->setTranslator($translator);

        $expected = $this->_getExpected('menu/translated.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testDisablingTranslation(): void
    {
        $translator = $this->_getTranslator();
        $this->_helper->setTranslator($translator);
        $this->_helper->setTranslatorEnabled(false);

        $expected = $this->_getExpected('menu/default1.html');
        $this->assertEquals($expected, $this->_helper->render());
    }

    public function testRenderingPartial(): void
    {
        $this->_helper->setPartial('menu.phtml');

        $expected = $this->_getExpected('menu/partial.html');
        $actual = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testRenderingPartialBySpecifyingAnArrayAsPartial(): void
    {
        $this->_helper->setPartial(['menu.phtml', 'application']);

        $expected = $this->_getExpected('menu/partial.html');
        $actual = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testRenderingPartialWithParams(): void
    {
        $this->_helper->setPartial(['menu_with_partial_params.phtml', 'application']);
        $expected = $this->_getExpected('menu/partial_with_params.html');
        $actual = $this->_helper->renderPartialWithParams(['variable' => 'test value']);
        $this->assertEquals($expected, $actual);
    }

    public function testRenderingPartialShouldFailOnInvalidPartialArray(): void
    {
        $this->_helper->setPartial(['menu.phtml']);
        $this->expectException(InvalidArgumentException::class);
        $this->_helper->render();
    }

    public function testSetMaxDepth(): void
    {
        $this->_helper->setMaxDepth(1);

        $expected = $this->_getExpected('menu/maxdepth.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testSetMinDepth(): void
    {
        $this->_helper->setMinDepth(1);

        $expected = $this->_getExpected('menu/mindepth.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testSetBothDepts(): void
    {
        $this->_helper->setMinDepth(1)->setMaxDepth(2);

        $expected = $this->_getExpected('menu/bothdepts.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testSetOnlyActiveBranch(): void
    {
        $this->_helper->setOnlyActiveBranch(true);

        $expected = $this->_getExpected('menu/onlyactivebranch.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testSetRenderParents(): void
    {
        $this->_helper->setOnlyActiveBranch(true)->setRenderParents(false);

        $expected = $this->_getExpected('menu/onlyactivebranch_noparents.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testSetOnlyActiveBranchAndMinDepth(): void
    {
        $this->_helper->setOnlyActiveBranch()->setMinDepth(1);

        $expected = $this->_getExpected('menu/onlyactivebranch_mindepth.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testOnlyActiveBranchAndMaxDepth(): void
    {
        $this->_helper->setOnlyActiveBranch()->setMaxDepth(2);

        $expected = $this->_getExpected('menu/onlyactivebranch_maxdepth.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testOnlyActiveBranchAndBothDepthsSpecified(): void
    {
        $this->_helper->setOnlyActiveBranch()->setMinDepth(1)->setMaxDepth(2);

        $expected = $this->_getExpected('menu/onlyactivebranch_bothdepts.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testOnlyActiveBranchNoParentsAndBothDepthsSpecified(): void
    {
        $this->_helper->setOnlyActiveBranch()
                      ->setMinDepth(1)
                      ->setMaxDepth(2)
                      ->setRenderParents(false);

        $expected = $this->_getExpected('menu/onlyactivebranch_np_bd.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    // @codingStandardsIgnoreStart
    private function _setActive(string $label): void
    {
        // @codingStandardsIgnoreEnd
        $container = $this->_helper->getContainer();

        foreach ($container->findAllByActive(true) as $page) {
            $page->setActive(false);
        }

        if ($p = $container->findOneByLabel($label)) {
            $p->setActive(true);
        }
    }

    public function testOnlyActiveBranchNoParentsActiveOneBelowMinDepth(): void
    {
        $this->_setActive('Page 2');

        $this->_helper->setOnlyActiveBranch()
                      ->setMinDepth(1)
                      ->setMaxDepth(1)
                      ->setRenderParents(false);

        $expected = $this->_getExpected('menu/onlyactivebranch_np_bd2.html');
        $actual = $this->_helper->renderMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testRenderSubMenuShouldOverrideOptions(): void
    {
        $this->_helper->setOnlyActiveBranch(false)
                      ->setMinDepth(1)
                      ->setMaxDepth(2)
                      ->setRenderParents(true);

        $expected = $this->_getExpected('menu/onlyactivebranch_noparents.html');
        $actual = $this->_helper->renderSubMenu();

        $this->assertEquals($expected, $actual);
    }

    public function testOptionMaxDepth(): void
    {
        $options = [
            'maxDepth' => 1,
        ];

        $expected = $this->_getExpected('menu/maxdepth.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionMinDepth(): void
    {
        $options = [
            'minDepth' => 1,
        ];

        $expected = $this->_getExpected('menu/mindepth.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionBothDepts(): void
    {
        $options = [
            'minDepth' => 1,
            'maxDepth' => 2,
        ];

        $expected = $this->_getExpected('menu/bothdepts.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionOnlyActiveBranch(): void
    {
        $options = [
            'onlyActiveBranch' => true,
        ];

        $expected = $this->_getExpected('menu/onlyactivebranch.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionOnlyActiveBranchNoParents(): void
    {
        $options = [
            'onlyActiveBranch' => true,
            'renderParents' => false,
        ];

        $expected = $this->_getExpected('menu/onlyactivebranch_noparents.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionOnlyActiveBranchAndMinDepth(): void
    {
        $options = [
            'minDepth' => 1,
            'onlyActiveBranch' => true,
        ];

        $expected = $this->_getExpected('menu/onlyactivebranch_mindepth.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionOnlyActiveBranchAndMaxDepth(): void
    {
        $options = [
            'maxDepth' => 2,
            'onlyActiveBranch' => true,
        ];

        $expected = $this->_getExpected('menu/onlyactivebranch_maxdepth.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionOnlyActiveBranchAndBothDepthsSpecified(): void
    {
        $options = [
            'minDepth' => 1,
            'maxDepth' => 2,
            'onlyActiveBranch' => true,
        ];

        $expected = $this->_getExpected('menu/onlyactivebranch_bothdepts.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testOptionOnlyActiveBranchNoParentsAndBothDepthsSpecified(): void
    {
        $options = [
            'minDepth' => 2,
            'maxDepth' => 2,
            'onlyActiveBranch' => true,
            'renderParents' => false,
        ];

        $expected = $this->_getExpected('menu/onlyactivebranch_np_bd.html');
        $actual = $this->_helper->renderMenu(null, $options);

        $this->assertEquals($expected, $actual);
    }

    public function testRenderingWithoutPageClassToLi(): void
    {
        $container = new Navigation($this->_nav2->toArray());
        $container->addPage([
            'label' => 'Class test',
            'uri' => 'test',
            'class' => 'foobar',
        ]);

        $expected = $this->_getExpected('menu/addclasstolistitem_as_false.html');
        $actual   = $this->_helper->renderMenu($container);

        $this->assertEquals(trim($expected), trim($actual));
    }

    public function testRenderingWithPageClassToLi(): void
    {
        $options = [
            'addClassToListItem' => true,
        ];

        $container = new Navigation($this->_nav2->toArray());
        $container->addPage([
            'label' => 'Class test',
            'uri' => 'test',
            'class' => 'foobar',
        ]);

        $expected = $this->_getExpected('menu/addclasstolistitem_as_true.html');
        $actual = $this->_helper->renderMenu($container, $options);

        $this->assertEquals(trim($expected), trim($actual));
    }

    public function testRenderDeepestMenuWithPageClassToLi(): void
    {
        $options = [
            'addClassToListItem' => true,
            'onlyActiveBranch' => true,
            'renderParents' => false,
        ];

        /** @var array[] $pages */
        $pages = $this->_nav2->toArray();
        $pages[1]['class'] = 'foobar';
        $container = new Navigation($pages);

        $expected = $this->_getExpected('menu/onlyactivebranch_addclasstolistitem.html');
        $actual = $this->_helper->renderMenu($container, $options);

        $this->assertEquals(trim($expected), trim($actual));
    }

    /**
     * Returns the contens of the expected $file, normalizes newlines.
     *
     * @param string $file
     *
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _getExpected($file)
    {
        // @codingStandardsIgnoreEnd
        return str_replace("\n", PHP_EOL, parent::_getExpected($file));
    }
}
