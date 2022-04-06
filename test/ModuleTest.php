<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\View\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testThatGetConfigReturnsANonEmptyArray(): void
    {
        $module = new Module();
        self::assertNotEmpty($module->getConfig());
    }
}
