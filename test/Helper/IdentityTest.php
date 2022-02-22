<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Identity as IdentityHelper;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    private function authService(?string $id): object
    {
        return new class ($id) {
            private ?string $id;

            public function __construct(?string $id)
            {
                $this->id = $id;
            }

            public function hasIdentity(): bool
            {
                return $this->id !== null;
            }

            public function getIdentity(): ?string
            {
                return $this->id;
            }
        };
    }

    public function testIdentityIsNullWhenTheAuthServiceDoesNotHaveAnIdentity(): void
    {
        $helper = new IdentityHelper($this->authService(null));
        self::assertNull($helper());
    }

    public function testIdentityIsNullWhenThereIsNoAuthServiceAtAll(): void
    {
        $helper = new IdentityHelper();
        self::assertNull($helper());
    }

    public function testIdentityIsTheExpectedValueWhenTheAuthServiceHasAnIdentity(): void
    {
        $helper = new IdentityHelper($this->authService('goat-man'));
        self::assertSame('goat-man', $helper());
    }

    public function testIdentityIsNullWhenTheAuthServiceDoesNotImplementHasIdentity(): void
    {
        $object = new class () {
        };
        $helper = new IdentityHelper($object);
        self::assertNull($helper());
    }

    public function testIdentityIsNullWhenTheAuthServiceDoesNotImplementGetIdentity(): void
    {
        $object = new class () {
            public function hasIdentity(): bool
            {
                return true;
            }
        };
        $helper = new IdentityHelper($object);
        self::assertNull($helper());
    }
}
