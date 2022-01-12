<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Response;
use Laminas\Json\Json as JsonFormatter;
use Laminas\View\Helper\Json as JsonHelper;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Laminas\View\Helper\Json
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class JsonTest extends TestCase
{
    private Response $response;
    private JsonHelper $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->response = new Response();
        $this->helper   = new JsonHelper();
        $this->helper->setResponse($this->response);
    }

    public function verifyJsonHeader(): void
    {
        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('Content-Type'));
        $header = $headers->get('Content-Type');
        self::assertInstanceOf(HeaderInterface::class, $header);
        $this->assertEquals('application/json', $header->getFieldValue());
    }

    public function testJsonHelperSetsResponseHeader(): void
    {
        $this->helper->__invoke('foobar');
        $this->verifyJsonHeader();
    }

    public function testJsonHelperReturnsJsonEncodedString(): void
    {
        $data = $this->helper->__invoke('foobar');
        $this->assertIsString($data);
        $this->assertEquals('foobar', JsonFormatter::decode($data));
    }

    public function testThatADeprecationErrorIsTriggeredWhenExpressionFinderOptionIsUsed(): void
    {
        $this->expectDeprecation();
        $this->helper->__invoke(['foo'], ['enableJsonExprFinder' => true]);
    }

    public function testThatADeprecationErrorIsNotTriggeredWhenExpressionFinderOptionIsNotUsed(): void
    {
        $this->expectNotToPerformAssertions();
        $this->helper->__invoke(['foo'], ['enableJsonExprFinder' => 'anything other than true']);
    }
}
