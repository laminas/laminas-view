<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use Laminas\Http\Response;
use Laminas\Json\Json as JsonFormatter;
use Laminas\View\Helper\Json as JsonHelper;

/**
 * Test class for Laminas\View\Helper\Json
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class JsonTest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->response = new Response();
        $this->helper   = new JsonHelper();
        $this->helper->setResponse($this->response);
    }

    public function verifyJsonHeader()
    {
        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('Content-Type'));
        $header = $headers->get('Content-Type');
        $this->assertEquals('application/json', $header->getFieldValue());
    }

    public function testJsonHelperSetsResponseHeader()
    {
        $json = $this->helper->__invoke('foobar');
        $this->verifyJsonHeader();
    }

    public function testJsonHelperReturnsJsonEncodedString()
    {
        $data = $this->helper->__invoke('foobar');
        $this->assertInternalType('string', $data);
        $this->assertEquals('foobar', JsonFormatter::decode($data));
    }
}
