<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Navigation;

use Laminas\Navigation\Navigation;
use Laminas\View\Helper\Navigation as NavigationHelper;

/**
 * @psalm-suppress MissingConstructor
 */
class AbstractHelperTest extends AbstractTest
{
    /**
     * View helper
     *
     * @var NavigationHelper\Breadcrumbs
     */
    protected $_helper; // phpcs:ignore

    protected function setUp(): void
    {
        $this->_helper = new NavigationHelper\Breadcrumbs();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->_helper) {
            $this->_helper->setDefaultAcl(null);
            $this->_helper->setAcl(null);
            $this->_helper->setDefaultRole(null);
            $this->_helper->setRole(null);
        }
    }

    public function testHasACLChecksDefaultACL(): void
    {
        $aclContainer = $this->getAcl();
        $acl          = $aclContainer['acl'];

        $this->assertEquals(false, $this->_helper->hasACL());
        $this->_helper->setDefaultAcl($acl);
        $this->assertEquals(true, $this->_helper->hasAcl());
    }

    public function testHasACLChecksMemberVariable(): void
    {
        $aclContainer = $this->getAcl();
        $acl          = $aclContainer['acl'];

        $this->assertEquals(false, $this->_helper->hasAcl());
        $this->_helper->setAcl($acl);
        $this->assertEquals(true, $this->_helper->hasAcl());
    }

    public function testHasRoleChecksDefaultRole(): void
    {
        $aclContainer = $this->getAcl();
        $role         = $aclContainer['role'];

        $this->assertEquals(false, $this->_helper->hasRole());
        $this->_helper->setDefaultRole($role);
        $this->assertEquals(true, $this->_helper->hasRole());
    }

    public function testHasRoleChecksMemberVariable(): void
    {
        $aclContainer = $this->getAcl();
        $role         = $aclContainer['role'];

        $this->assertEquals(false, $this->_helper->hasRole());
        $this->_helper->setRole($role);
        $this->assertEquals(true, $this->_helper->hasRole());
    }

    public function testEventManagerIsNullByDefault(): void
    {
        $this->assertNull($this->_helper->getEventManager());
    }

    public function testFallBackForContainerNames(): void
    {
        // Register navigation service with name equal to the documentation
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            'navigation',
            $this->serviceManager->get('Navigation')
        );
        $this->serviceManager->setAllowOverride(false);

        $this->_helper->setServiceLocator($this->serviceManager);

        $this->_helper->setContainer('navigation');
        $this->assertInstanceOf(
            Navigation::class,
            $this->_helper->getContainer()
        );

        $this->_helper->setContainer('default');
        $this->assertInstanceOf(
            Navigation::class,
            $this->_helper->getContainer()
        );
    }
}
