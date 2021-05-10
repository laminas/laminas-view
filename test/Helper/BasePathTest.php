<?php

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\BasePath;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class BasePathTest extends TestCase
{
    public function testBasePathWithoutFile(): void
    {
        $helper = new BasePath();
        $helper->setBasePath('/foo');

        $this->assertEquals('/foo', $helper());
    }

    public function testBasePathWithFile(): void
    {
        $helper = new BasePath();
        $helper->setBasePath('/foo');

        $this->assertEquals('/foo/bar', $helper('bar'));
    }

    public function testBasePathNoDoubleSlashes(): void
    {
        $helper = new BasePath();
        $helper->setBasePath('/');

        $this->assertEquals('/', $helper('/'));
    }

    public function testBasePathWithFilePrefixedBySlash(): void
    {
        $helper = new BasePath();
        $helper->setBasePath('/foo');

        $this->assertEquals('/foo/bar', $helper('/bar'));
    }
}
