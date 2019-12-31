<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Placeholder as PlaceholderHelper;
use Laminas\View\Renderer\PhpRenderer as View;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class RenderToPlaceholderTest extends \PHPUnit_Framework_TestCase
{

    protected $_view = null;

    public function setUp()
    {
        $this->_view = new View();
        $this->_view->resolver()->addPath(__DIR__.'/_files/scripts/');
    }

    public function testDefaultEmpty()
    {
        $this->_view->plugin('renderToPlaceholder')->__invoke('rendertoplaceholderscript.phtml', 'fooPlaceholder');
        $placeholder = new PlaceholderHelper();
        $this->assertEquals("Foo Bar" . "\n", $placeholder->__invoke('fooPlaceholder')->getValue());
    }

}

