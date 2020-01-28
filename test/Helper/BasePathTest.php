<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use Laminas\View\Helper\BasePath;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class BasePathTest extends TestCase
{
    public function testBasePathWithoutFile()
    {
        $helper = new BasePath();
        $helper->setBasePath('/foo');

        $this->assertEquals('/foo', $helper());
    }

    public function testBasePathWithFile()
    {
        $helper = new BasePath();
        $helper->setBasePath('/foo');

        $this->assertEquals('/foo/bar', $helper('bar'));
    }

    public function testBasePathNoDoubleSlashes()
    {
        $helper = new BasePath();
        $helper->setBasePath('/');

        $this->assertEquals('/', $helper('/'));
    }

    public function testBasePathWithFilePrefixedBySlash()
    {
        $helper = new BasePath();
        $helper->setBasePath('/foo');

        $this->assertEquals('/foo/bar', $helper('/bar'));
    }
}
