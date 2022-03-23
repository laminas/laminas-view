<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Identity as IdentityHelper;
use LaminasTest\View\Helper\TestAsset\AuthenticationServiceStub;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    private function authService(?string $id): AuthenticationServiceStub
    {
        return new AuthenticationServiceStub($id);
    }

    public function testIdentityIsNullWhenTheAuthServiceDoesNotHaveAnIdentity(): void
    {
        $helper = new IdentityHelper($this->authService(null));
        self::assertNull($helper());
    }

    public function testAnExceptionIsThrownWhenThereIsNoAuthServiceAtAll(): void
    {
        $helper = new IdentityHelper();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No AuthenticationServiceInterface instance provided');
        self::assertNull($helper());
    }

    public function testIdentityIsTheExpectedValueWhenTheAuthServiceHasAnIdentity(): void
    {
        $helper = new IdentityHelper($this->authService('goat-man'));
        self::assertSame('goat-man', $helper());
    }
}
