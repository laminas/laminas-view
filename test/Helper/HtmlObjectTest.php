<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HtmlObject;
use PHPUnit\Framework\TestCase;

/** @psalm-import-type DoctypeID from Doctype */
final class HtmlObjectTest extends TestCase
{
    private HtmlObject $helper;

    protected function setUp(): void
    {
        $this->helper = new HtmlObject(
            new Escaper(),
            new Doctype(),
        );
    }

    /** @param DoctypeID $doctype */
    private function setDoctype(string $doctype): void
    {
        $this->helper = new HtmlObject(
            new Escaper(),
            new Doctype($doctype),
        );
    }

    public function testMakeHtmlObjectWithoutAttribsWithoutParams(): void
    {
        $htmlObject = $this->helper->__invoke('datastring', 'typestring');

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);
    }

    public function testMakeHtmlObjectWithAttribsWithoutParams(): void
    {
        $attribs = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', $attribs);

        $this->assertStringContainsString(
            '<object data="datastring" type="typestring" key1="value1" key2="value2">',
            $htmlObject,
        );
        $this->assertStringContainsString('</object>', $htmlObject);
    }

    public function testMakeHtmlObjectWithoutAttribsWithParamsHtml(): void
    {
        $this->setDoctype(Doctype::HTML4_STRICT);

        $params = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', [], $params);

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);

        foreach ($params as $key => $value) {
            $param = '<param name="' . $key . '" value="' . $value . '">';

            $this->assertStringContainsString($param, $htmlObject);
        }
    }

    public function testMakeHtmlObjectWithoutAttribsWithParamsXhtml(): void
    {
        $this->setDoctype(Doctype::XHTML1_STRICT);

        $params = [
            'paramname1' => 'paramvalue1',
            'paramname2' => 'paramvalue2',
        ];

        $htmlObject = $this->helper->__invoke('datastring', 'typestring', [], $params);

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);

        foreach ($params as $key => $value) {
            $param = '<param name="' . $key . '" value="' . $value . '" />';

            $this->assertStringContainsString($param, $htmlObject);
        }
    }

    public function testMakeHtmlObjectWithContent(): void
    {
        $htmlObject = $this->helper->__invoke('datastring', 'typestring', [], [], 'testcontent');

        $this->assertStringContainsString('<object data="datastring" type="typestring">', $htmlObject);
        $this->assertStringContainsString('testcontent', $htmlObject);
        $this->assertStringContainsString('</object>', $htmlObject);
    }

    public function testFallbackContentIsNotEscaped(): void
    {
        $html = $this->helper->__invoke('foo', 'bar', [], [], '<p>Baz</p>');
        self::assertStringContainsString('<p>Baz</p>', $html);
    }
}
