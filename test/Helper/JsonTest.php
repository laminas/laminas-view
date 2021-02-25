<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Response;
use Laminas\View\Helper\Json as JsonHelper;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Test class for Laminas\View\Helper\Json
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class JsonTest extends TestCase
{
    /** @var Response */
    private $response;
    /** @var JsonHelper */
    private $helper;

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

    private function verifyJsonHeader(): void
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
        $input = [
            'dory' => 'blue',
            'nemo' => 'orange',
        ];
        $expect = json_encode($input, JSON_THROW_ON_ERROR);

        self::assertJsonStringEqualsJsonString($expect, ($this->helper)($input));
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
