<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\NonPersistent as NonPersistentStorage;
use Laminas\View\Helper\Identity as IdentityHelper;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    public function testGetIdentity(): void
    {
        $identity = new TestAsset\IdentityObject();
        $identity->setUsername('a username');
        $identity->setPassword('a password');

        $authenticationService = new AuthenticationService(
            new NonPersistentStorage(),
            new TestAsset\AuthenticationAdapter()
        );

        $identityHelper = new IdentityHelper();
        $identityHelper->setAuthenticationService($authenticationService);

        $this->assertNull($identityHelper());

        $this->assertFalse($authenticationService->hasIdentity());

        $authenticationService->getAdapter()->setIdentity($identity);
        $result = $authenticationService->authenticate();
        $this->assertTrue($result->isValid());

        $this->assertEquals($identity, $identityHelper());
    }
}
