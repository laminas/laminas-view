<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\NonPersistent as NonPersistentStorage;
use Laminas\View\Helper\Identity as IdentityHelper;

/**
 * Laminas_View_Helper_IdentityTest
 *
 * Tests Identity helper
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class IdentityTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIdentity()
    {
        $identity = new TestAsset\IdentityObject();
        $identity->setUsername('a username');
        $identity->setPassword('a password');

        $authenticationService = new AuthenticationService(
            new NonPersistentStorage,
            new TestAsset\AuthenticationAdapter
        );

        $identityHelper = new IdentityHelper;
        $identityHelper->setAuthenticationService($authenticationService);

        $this->assertNull($identityHelper());

        $this->assertFalse($authenticationService->hasIdentity());

        $authenticationService->getAdapter()->setIdentity($identity);
        $result = $authenticationService->authenticate();
        $this->assertTrue($result->isValid());

        $this->assertEquals($identity, $identityHelper());
    }
}
